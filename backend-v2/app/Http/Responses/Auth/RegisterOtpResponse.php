<?php

namespace App\Http\Responses\Auth;

use App\Enums\UserRole;
use App\Mail\RegisterEmailOtpMail;
use App\Support\EmailOtpStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Throwable;

class RegisterOtpResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if (
            $user !== null
            && $user->hasRole(UserRole::PublicUser)
            && $user->email_verified_at === null
        ) {
            try {
                $code = EmailOtpStore::issue(EmailOtpStore::userKey($user->id));

                Mail::to($user->email)->send(new RegisterEmailOtpMail($user, $code));

                return $request->wantsJson()
                    ? new JsonResponse([
                        'redirect' => route('register.otp.show'),
                    ], 201)
                    : redirect()->route('register.otp.show')->with('status', 'otp-sent');
            } catch (Throwable $exception) {
                report($exception);

                return $request->wantsJson()
                    ? new JsonResponse([
                        'redirect' => route('register.otp.show'),
                        'status' => 'otp-send-failed',
                    ], 201)
                    : redirect()->route('register.otp.show')->with('status', 'otp-send-failed');
            }
        }

        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended(config('fortify.home'));
    }
}
