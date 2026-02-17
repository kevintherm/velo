<?php

namespace App\Delivery\Http\Controllers;

use App\Delivery\Entity\FileObject;
use App\Delivery\Entity\SafeCollection;
use App\Delivery\Http\Requests\RecordRequest;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Field\Enums\FieldType;
use App\Domain\Project\Exceptions\InvalidRuleException;
use App\Domain\Record\Actions\CreateRecord;
use App\Domain\Record\Actions\ListRecords;
use App\Domain\Record\Actions\ProcessRecordUploadedFiles;
use App\Domain\Record\Actions\UpdateRecord;
use App\Domain\Record\Authorization\RuleContext;
use App\Domain\Record\Resources\RecordResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Response;

class RecordController extends Controller
{
    /**
     * @throws InvalidRuleException
     */
    public function list(RecordRequest $request, Collection $collection): JsonResponse
    {
        $perPage = $request->input('per_page', 100);
        $page = $request->input('page', 1);
        $filter = $request->input('filter', '');
        $sort = $request->input('sort', '');
        $expand = $request->input('expand', '');

        $resources = app(ListRecords::class)->execute(
            $collection,
            $perPage,
            $page,
            $filter,
            $sort,
            $expand,
            RuleContext::fromRequest($request),
        );

        return $this->success($resources);
    }

    public function view(RecordRequest $request, Collection $collection, string $recordId): JsonResponse
    {
        $expand = $request->input('expand', '');

        $record = $collection->records()
            ->filter('id', '=', $recordId)
            ->expandFromString($expand)
            ->firstOrFail();

        $record->setRelation('collection', $collection);
        $resource = new RecordResource($record);

        return $resource->response();
    }

    public function create(RecordRequest $request, Collection $collection)
    {
        $record = app(CreateRecord::class)->execute($request, $collection);
        $resource = new RecordResource($record);
        return $resource->response();
    }

    public function update(RecordRequest $request, Collection $collection, string $recordId): JsonResponse
    {
        $record = app(UpdateRecord::class)->execute($request, $collection, $recordId);
        $resource = new RecordResource($record);
        return $resource->response();
    }

    public function delete(RecordRequest $request, Collection $collection, string $recordId): JsonResponse
    {
        $record = $collection->records()->filter('id', '=', $recordId)->firstRawOrFail();
        $record->delete();
        return Response::json([], 204);
    }
}
