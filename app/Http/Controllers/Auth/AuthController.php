<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use Illuminate\Http\Request;
use App\Support\EmailOtp;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
    private const DASHBOARD_ROLES = ['admin', 'super_admin', 'noc_user', RoleEnum::APPLICANT];

    public function login(LoginRequest $request, EmailOtp $otp)
    {
        $request->authenticate();

        $user = Auth::user();
        $role = optional($user->mainRole())->name;

        if (! $user->active()) {
            Auth::logout();

            // applicants who never finished email verification get a fresh code instead of a dead end
            if ($role === RoleEnum::APPLICANT && $user->status === UserStatusEnum::INACTIVE->value && ! $user->email_verified_at) {
                if ($otp->canResend($user->email, EmailOtp::PURPOSE_REGISTER)) {
                    $otp->send($user->email, EmailOtp::PURPOSE_REGISTER);
                }
                $request->session()->put('register_email', $user->email);

                return redirect()->route('register.verify')
                    ->with('success', __('Please verify your email. We have sent a code to your inbox.'));
            }

            return back()->withInput($request->only('email'))
                ->withErrors(['active' => __('You must be an active user')]);
        }

        if (! in_array($role, self::DASHBOARD_ROLES, true)) {
            Auth::logout();

            return back()->withInput($request->only('email'))
                ->withErrors(['active' => __('You must be an active user')]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
