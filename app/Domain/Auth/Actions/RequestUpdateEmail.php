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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestUpdateEmail
{
    public function execute(Request $request, Collection $collection): void
    {
        if ($collection->type !== CollectionType::Auth) {
            throw new BadRequestHttpException('Collection is not auth enabled.');
        }

        $request->validate([
            'id'    => 'required_without:email',
            'email' => 'required_without:id|email',
        ]);

        $id = $request->input('id');
        $email = $request->input('email');
        $record = $collection->records()->filter('email', '=', $email)->orFilter('id', '=', $id)->first();

        if (! $record) {
            return;
        }

        $otpLength = (int) ($collection->options['auth_methods']['otp']['config']['generate_password_length'] ?? 6);
        [$otp, $hashed] = app(OtpService::class)->generate($otpLength);

        $duration = (int) $collection->options['other']['tokens_options']['email_change_duration']['value'] ?? 1800;
        $expiresAt = now()->addSeconds($duration);

        AuthOtp::create([
            'project_id'    => $collection->project_id,
            'collection_id' => $collection->id,
            'record_id'     => $record->id,
            'token_hash'    => $hashed,
            'action'        => OtpType::EMAIL_CHANGE,
            'expires_at'    => $expiresAt,
            'ip_address'    => $request->ip(),
            'device_name'   => $request->input('device_name'),
        ]);

        Mail::to($email)->queue(new Otp($otp, $duration, $collection, $collection->project->name));
    }
}
