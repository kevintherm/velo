<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\AuthSession;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class LogoutAll
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $session = $request->user();
        if (! $session || ! $session->meta?->_id) {
            throw new UnauthorizedHttpException('Unauthorized.');
        }

        AuthSession::where('record_id', $session->meta->_id)
            ->where('collection_id', $collection->id)
            ->update([
                'expires_at' => now(),
            ]);

        // Hook: auth.logout
        \App\Domain\Hooks\Facades\Hooks::trigger('auth.logout', [
            'collection'   => $collection,
            'record_id'    => $session->meta->_id,
            'all_sessions' => true,
        ]);
    }
}
