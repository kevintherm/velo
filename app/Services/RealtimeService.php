<?php

namespace App\Services;

use App\Entity\SafeCollection;
use App\Events\RealtimeMessage;
use App\Http\Resources\RecordResource;
use App\Models\RealtimeConnection;
use App\Models\Record;
use App\Services\EvaluateRuleExpression;

class RealtimeService
{
    public function __construct(
        protected FilterMatchingService $filterMatcher
    ) {
    }

    public function dispatchUpdates(Record $record, string $action): void
    {
        $listRule = $record->collection->api_rules['list'] ?? '';

        RealtimeConnection::query()
            ->join('records', function ($join) {
                $join->on('records.id', '=', 'realtime_connections.record_id')
                    ->on('records.collection_id', '=', 'realtime_connections.collection_id');
            })
            ->select(['channel_name', 'is_public', 'record_id', 'filter', 'records.data AS user'])
            ->where('realtime_connections.collection_id', $record->collection_id)
            ->chunk(500, function ($connections) use ($record, $action, $listRule) {
                foreach ($connections as $connection) {
                    $combinedFilter = $this->buildCombinedFilter(
                        $connection->user,
                        $connection->filter,
                        $listRule,
                    );

                    if ($this->filterMatcher->match($record, $combinedFilter)) {
                        RealtimeMessage::dispatch($connection->channel_name, [
                            'action' => $action,
                            'record' => (new RecordResource($record))->resolve()
                        ], $connection->is_public);
                    }
                }
            });
    }

    /**
     * Build combined filter from connection filter and interpolated list rule.
     * The list rule is interpolated with the connection's record_id as @request.auth.id
     * Since we're only getting the user, other sys_request like body, query, param does not exists
     */
    protected function buildCombinedFilter($userData, ?string $connectionFilter, string $listRule): string
    {
        $filter = $connectionFilter ?? '';

        if (empty($listRule) || $listRule === 'SUPERUSER_ONLY') {
            return $filter;
        }

        $context = [
            'sys_request' => (object) [
                'auth' => new SafeCollection(json_decode($userData, true)),
                'body' => null,
                'param' => null,
                'query' => null,
            ],
        ];

        $interpolatedRule = app(EvaluateRuleExpression::class)
            ->forExpression($listRule)
            ->withContext($context)
            ->interpolate();

        if (empty($filter)) {
            return $interpolatedRule;
        }

        return "$filter AND $interpolatedRule";
    }
}
