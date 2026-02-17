<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpType;
use App\Domain\Auth\Models\AuthOtp;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfirmUpdateEmail
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $request->validate([
            'otp'       => 'required|string',
            'new_email' => ['required', 'email'],
        ]);

        $reset = AuthOtp::where('collection_id', $collection->id)
            ->where('action', OtpType::EMAIL_CHANGE)
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

        $record->data->put('email', $request->input('new_email'));
        $record->save();

        $reset->used_at = now();
        $reset->save();
    }
}
