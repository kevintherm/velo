<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpType;
use App\Domain\Auth\Models\AuthOtp;
use App\Domain\Auth\Models\Mail\Otp;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Auth\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForgotPassword
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        if (! isset($collection->options['auth_methods']['standard']) || ! $collection->options['auth_methods']['standard']['enabled']) {
            throw new BadRequestHttpException('Collection is not setup for standard auth method.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        $filterString = "email = '{$email}'";
        $record = $collection->records()->filterFromString($filterString)->first();

        if (! $record) {
            return;
        }

        $otpLength = (int) ($collection->options['auth_methods']['otp']['config']['generate_password_length'] ?? 6);
        [$otp, $hashed] = app(OtpService::class)->generate($otpLength);
        $expires = (int) $collection->options['other']['tokens_options']['password_reset_duration']['value'] ?? 1800;

        AuthOtp::create([
            'project_id'    => $collection->project_id,
            'collection_id' => $collection->id,
            'record_id'     => $record->id,
            'token_hash'    => $hashed,
            'action'        => OtpType::PASSWORD_RESET,
            'expires_at'    => now()->addSeconds($expires),
            'ip_address'    => $request->ip(),
            'device_name'   => $request->input('device_name'),
        ]);

        Mail::to($email)->queue(new Otp($otp, $expires, $collection, config('app.name')));
    }
}
