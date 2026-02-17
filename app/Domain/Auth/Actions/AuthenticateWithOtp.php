<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Enums\OtpType;
use App\Domain\Auth\Models\AuthOtp;
use App\Domain\Auth\Models\AuthSession;
use App\Domain\Auth\Models\Mail\LoginAlert;
use App\Domain\Collection\Enums\CollectionType;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuthenticateWithOtp
{
    public function execute(Request $request, Collection $collection): string
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $request->validate([
            'email'       => 'required|email',
            'otp'         => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        $record = $collection->records()->filter('email', '=', $email)->first();

        if (! $record) {
            throw new NotFoundHttpException('User with associated email not found.');
        }

        $authOtp = AuthOtp::where('collection_id', $collection->id)
            ->where('action', OtpType::AUTHENTICATION)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->where('token_hash', hash('sha256', $otp))
            ->first();

        if (! $authOtp) {
            throw new BadRequestHttpException('Invalid or expired OTP.');
        }

        $authOtp->used_at = now();
        $authOtp->save();

        [$token, $hashed] = AuthSession::generateToken();

        $authTokenExpires = (int) ($collection->options['other']['tokens_options']['auth_duration']['value'] ?? 604800);

        $session = AuthSession::create([
            'project_id'    => $collection->project_id,
            'collection_id' => $collection->id,
            'record_id'     => $record->id,
            'token_hash'    => $hashed,
            'expires_at'    => now()->addSeconds($authTokenExpires),
            'last_used_at'  => now(),
            'device_name'   => $request->input('device_name'),
            'ip_address'    => $request->ip(),
        ]);

        $isNewIp = ! AuthSession::where('record_id', $record->id)
            ->where('collection_id', $collection->id)
            ->where('ip_address', $request->ip())
            ->where('id', '!=', $session->id)
            ->exists();

        if ($isNewIp && isset($collection->options['mail_templates']['login_alert']['body']) && ! empty($collection->options['mail_templates']['login_alert']['body'])) {
            if ($email) {
                Mail::to($email)->queue(new LoginAlert(
                    $collection,
                    $record,
                    $request->input('device_name'),
                    $request->ip()
                ));
            }
        }

        return $token;
    }
}
