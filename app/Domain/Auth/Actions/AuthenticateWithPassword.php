<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\AuthSession;
use App\Domain\Auth\Models\Mail\LoginAlert;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Project\Exceptions\InvalidRuleException;
use App\Domain\Record\Authorization\RuleContext;
use App\Domain\Record\Models\Record;
use App\Domain\Record\Services\RecordQuery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticateWithPassword
{
    /**
     * @throws InvalidRuleException
     * @throws BadRequestHttpException
     * @throws UnauthorizedHttpException
     * @throws ValidationException
     */
    public function execute(Request $request, Collection $collection): string
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        if (! isset($collection->options['auth_methods']['standard'])) {
            throw new BadRequestHttpException('Collection is not setup for standard auth method.');
        }

        if (! $collection->options['auth_methods']['standard']['enabled']) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $identifiers = $collection->options['auth_methods']['standard']['fields'];

        $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $validFields = $collection->fields()->whereIn('name', $identifiers)->pluck('name')->toArray();
        $identifiers = array_filter($identifiers, fn ($field) => in_array($field, $validFields));

        if (empty($identifiers)) {
            throw new BadRequestHttpException('Collection is not setup for standard auth method.');
        }

        $identifierValue = $request->input('identifier');
        $conditions = array_map(fn ($field) => ['field' => $field, 'value' => $identifierValue], $identifiers);
        $filterString = RecordQuery::buildFilterString($conditions, 'OR');
        $record = $collection->records()->filterFromString($filterString)->first();

        if (! $record) {
            throw ValidationException::withMessages(['identifier' => 'Invalid credentials.']);
        }

        if (! Hash::check($request->input('password'), $record->data->password)) {
            throw ValidationException::withMessages(['identifier' => 'Invalid credentials.']);
        }

        $authenticateRule = $collection->api_rules['authenticate'] ?? '';
        if ($authenticateRule !== '') {
            $context = RuleContext::fromRequest($request, $record->data->toArray());

            $rule = app(\App\Delivery\Services\EvaluateRuleExpression::class)
                ->forExpression($authenticateRule)
                ->withContext($context);

            if (! $rule->evaluate()) {
                throw ValidationException::withMessages(['identifier' => 'Authentication failed due to collection rules.']);
            }
        }

        [$token, $hashed] = AuthSession::generateToken();

        $isNewIp = ! AuthSession::where('record_id', $record->id)
            ->where('collection_id', $collection->id)
            ->where('ip_address', $request->ip())
            ->exists();

        $authTokenExpires = (int) $collection->options['other']['tokens_options']['auth_duration']['value'] ?? 604800;
        AuthSession::create([
            'project_id'    => $collection->project_id,
            'collection_id' => $collection->id,
            'record_id'     => $record->id,
            'token_hash'    => $hashed,
            'expires_at'    => now()->addSeconds($authTokenExpires),
            'last_used_at'  => now(),
            'device_name'   => $request->input('device_name'),
            'ip_address'    => $request->ip(),
        ]);

        if ($isNewIp && isset($collection->options['mail_templates']['login_alert']['body']) && ! empty($collection->options['mail_templates']['login_alert']['body'])) {
            $email = $record->data->email;
            if ($email) {
                Mail::to($email)->queue(new LoginAlert(
                    $collection,
                    $record,
                    $request->input('device_name'),
                    $request->ip()
                ));
            }
        }

        // Hook: auth.login
        \App\Domain\Hooks\Facades\Hooks::trigger('auth.login', [
            'collection' => $collection,
            'record'     => $record->data->toArray(),
            'record_id'  => $record->id,
            'token'      => $token,
            'ip_address' => $request->ip(),
        ]);

        return $token;
    }
}
