<?php

namespace App\Delivery\Http\Controllers;

use App\Domain\Auth\Actions\AuthenticateWithOtp;
use App\Domain\Auth\Actions\AuthenticateWithPassword;
use App\Domain\Auth\Actions\ConfirmForgotPassword;
use App\Domain\Auth\Actions\ConfirmUpdateEmail;
use App\Domain\Auth\Actions\ForgotPassword;
use App\Domain\Auth\Actions\GetAuthenticatedUser;
use App\Domain\Auth\Actions\Logout;
use App\Domain\Auth\Actions\LogoutAll;
use App\Domain\Auth\Actions\RequestAuthOtp;
use App\Domain\Auth\Actions\RequestUpdateEmail;
use App\Domain\Auth\Actions\ResetPassword;
use App\Domain\Collection\Models\Collection;
use App\Domain\Record\Resources\RecordResource;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @throws \App\Domain\Project\Exceptions\InvalidRuleException
     */
    public function authenticateWithPassword(Request $request, Collection $collection)
    {
        $token = app(AuthenticateWithPassword::class)->execute($request, $collection);

        return $this->success($token);
    }

    public function me(Request $request, Collection $collection)
    {
        $record = app(GetAuthenticatedUser::class)->execute($request, $collection);
        $resource = new RecordResource($record);

        return $this->success($resource);
    }

    public function logout(Request $request, Collection $collection)
    {
        app(Logout::class)->execute($request, $collection);

        return $this->success(null, 'Logged out.');
    }

    public function logoutAll(Request $request, Collection $collection)
    {
        app(LogoutAll::class)->execute($request, $collection);

        return $this->success(null, 'Logged out from all sessions.');
    }

    public function resetPassword(Request $request, Collection $collection)
    {
        app(ResetPassword::class)->execute($request, $collection);

        return $this->success(null, 'Password reset successful.');
    }

    public function forgotPassword(Request $request, Collection $collection)
    {
        app(ForgotPassword::class)->execute($request, $collection);

        return $this->success(null, 'If an account exists with this email, you will receive a password reset token.');
    }

    public function confirmForgotPassword(Request $request, Collection $collection)
    {
        app(ConfirmForgotPassword::class)->execute($request, $collection);

        return $this->success(null, 'Password reset successful.');
    }

    public function requestAuthOtp(Request $request, Collection $collection)
    {
        app(RequestAuthOtp::class)->execute($request, $collection);

        return $this->success(null, 'If an account exists with this email, you will receive a login code.');
    }

    public function authenticateWithOtp(Request $request, Collection $collection)
    {
        $token = app(AuthenticateWithOtp::class)->execute($request, $collection);

        return $this->success($token, 'Authenticated.');
    }

    public function requestUpdateEmail(Request $request, Collection $collection)
    {
        app(RequestUpdateEmail::class)->execute($request, $collection);

        return $this->success(null, 'If an account exists with this email, you will receive a authorization code.');
    }

    public function confirmUpdateEmail(Request $request, Collection $collection)
    {
        app(ConfirmUpdateEmail::class)->execute($request, $collection);

        return $this->success(null, 'Email updated successfully.');
    }
}
