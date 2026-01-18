<?php

namespace App\Http\Middleware;

use App\Models\AuthSession;
use App\Models\Record;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthMiddleware
{
    /**
     * Inject user record for authentication.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, \Closure $next): Response
    {
        $token = $request->bearerToken();
        $hash = hash('sha256', $token);

        if (! $token) {
            $this->handleGuest($request);
            return $next($request);
        }

        $session = AuthSession::query()
            ->join('records', function ($join) {
                $join->on('records.id', '=', 'auth_sessions.record_id')
                    ->on('records.collection_id', '=', 'auth_sessions.collection_id');
            })
            ->where('auth_sessions.token_hash', $hash)
            ->where('auth_sessions.expires_at', '>', now())
            ->select([
                'auth_sessions.record_id',
                'auth_sessions.collection_id',
                'auth_sessions.project_id',
                'auth_sessions.last_used_at',
                'records.data AS user',
            ])
            ->first();

        if (! $session) {
            $this->handleGuest($request);
            return $next($request);
        }

        $recordData = json_decode($session->user, true);

        $request->merge([
            'auth' => collect([
                ...$recordData,
                'meta' => collect([
                    '_id' => $session->record_id,
                    'collection_id' => $session->collection_id,
                    'project_id' => $session->project_id,
                ]),
            ]),
        ]);

        $threshold = config('larabase.session_defer_threshold') ?? 150;
        if ($session->last_used_at->diffInSeconds(now()) > $threshold) {
            $session->update(['last_used_at' => now()]);
        }

        return $next($request);
    }

    private function handleGuest($request): void
    {
        $request->attributes->set('auth', (object) [
            'id' => null,
            'name' => null,
            'email' => null,
            'meta' => (object) [
                '_id' => null,
                'collection_id' => null,
                'project_id' => null,
            ],
        ]);

    }
}
