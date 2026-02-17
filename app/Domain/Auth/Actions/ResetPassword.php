<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Models\AuthSession;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class ResetPassword
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

        $request->validate([
            'password'            => 'required|string',
            'new_password'        => ['required', 'string', Password::min(8)],
            'invalidate_sessions' => 'boolean',
        ]);

        $record = Record::find($session->meta->_id);
        if (! $record) {
            throw new NotFoundHttpException('User not found.');
        }

        if (! Hash::check($request->input('password'), $record->data->password)) {
            throw new BadRequestHttpException('Invalid current password.');
        }

        $record->data->put('password', Hash::make($request->input('new_password')));
        $record->save();

        if ($request->input('invalidate_sessions')) {
            AuthSession::where('record_id', $session->meta->_id)
                ->where('collection_id', $collection->id)
                ->update([
                    'expires_at' => now(),
                ]);
        }

        // Hook: auth.password_reset
        \App\Domain\Hooks\Facades\Hooks::trigger('auth.password_reset', [
            'collection' => $collection,
            'record'     => $record->data->toArray(),
            'record_id'  => $record->id,
            'by_admin'   => false,
        ]);
    }
}
