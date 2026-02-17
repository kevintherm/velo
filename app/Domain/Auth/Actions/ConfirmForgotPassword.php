<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpType;
use App\Domain\Auth\Models\AuthOtp;
use App\Domain\Auth\Models\AuthSession;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfirmForgotPassword
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $request->validate([
            'otp'                 => 'required|string',
            'new_password'        => ['required', 'string', Password::min(8), 'confirmed'],
            'invalidate_sessions' => 'boolean',
        ]);

        $reset = AuthOtp::where('collection_id', $collection->id)
            ->where('action', OtpType::PASSWORD_RESET)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('token_hash', hash('sha256', $request->input('otp')))
            ->first();

        if (! $reset) {
            throw new BadRequestHttpException('Invalid code.');
        }

        $record = $reset->record;

        if (! $record) {
            throw new NotFoundHttpException('User associated with this request no longer exists.');
        }

        $record->data->put('password', Hash::make($request->input('new_password')));
        $record->save();

        $reset->used_at = now();
        $reset->save();

        if ($request->boolean('invalidate_sessions')) {
            AuthSession::where('record_id', $record->id)
                ->where('collection_id', $collection->id)
                ->delete();
        }

        // Hook: auth.password_reset
        \App\Domain\Hooks\Facades\Hooks::trigger('auth.password_reset', [
            'collection' => $collection,
            'record'     => $record->data->toArray(),
            'record_id'  => $record->id,
            'by_admin'   => false,
            'via_otp'    => true,
        ]);
    }
}
