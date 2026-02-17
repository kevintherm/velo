<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpType;
use App\Domain\Auth\Models\AuthOtp;
use App\Domain\Auth\Models\Mail\Otp;
use App\Domain\Auth\Services\OtpService;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestAuthOtp
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        if (! isset($collection->options['auth_methods']['otp']) || ! $collection->options['auth_methods']['otp']['enabled']) {
            throw new BadRequestHttpException('OTP authentication is not enabled.');
        }

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $record = $collection->records()->filter('email', '=', $email)->first();

        if (! $record) {
            return;
        }

        $otpLength = (int) ($collection->options['auth_methods']['otp']['config']['generate_password_length'] ?? 6);
        [$otp, $hashed] = app(OtpService::class)->generate($otpLength);

        $duration = (int) ($collection->options['auth_methods']['otp']['config']['duration_s'] ?? 180);
        $expiresAt = now()->addSeconds($duration);

        AuthOtp::create([
            'project_id'    => $collection->project_id,
            'collection_id' => $collection->id,
            'record_id'     => $record->id,
            'token_hash'    => $hashed,
            'action'        => OtpType::AUTHENTICATION,
            'expires_at'    => $expiresAt,
            'ip_address'    => $request->ip(),
            'device_name'   => $request->input('device_name'),
        ]);

        Mail::to($email)->queue(new Otp($otp, $duration, $collection, $collection->project->name));
    }
}
