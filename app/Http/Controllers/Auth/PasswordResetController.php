<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{

    public function __construct(protected EmailOtp $otp)
    {
    }

    public function index()
    {
        return view('auth.forgot-password');
    }

    public function sendOtp(Request $request)
    {
        $email = $request->validate(['email' => ['required', 'email']])['email'];

        // same response whether or not the account exists, so emails can't be probed
        if (User::where('email', $email)->exists() && $this->otp->canResend($email, EmailOtp::PURPOSE_RESET)) {
            $this->otp->send($email, EmailOtp::PURPOSE_RESET);
        }

        $request->session()->forget('reset_verified_email');
        $request->session()->put('reset_email', $email);

        return redirect()->route('password.verify')
            ->with('success', __('If an account exists for that email, we have sent a reset code to it.'));
    }

    public function verifyOtpIndex(Request $request)
    {
        $email = $request->session()->get('reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        return view('auth.verify-otp', [
            'email' => $email,
            'action' => route('password.verify.submit'),
            'resendAction' => route('password.resend'),
            'title' => __('Enter reset code'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $email = $request->session()->get('reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        if (! $this->otp->verify($email, EmailOtp::PURPOSE_RESET, $request->input('otp'))) {
            return back()->withErrors(['otp' => __('The code is invalid or has expired.')]);
        }

        $request->session()->forget('reset_email');
        $request->session()->put('reset_verified_email', $email);

        return redirect()->route('password.reset');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        if (! $this->otp->canResend($email, EmailOtp::PURPOSE_RESET)) {
            return back()->withErrors(['otp' => __('Please wait a minute before requesting a new code.')]);
        }

        if (User::where('email', $email)->exists()) {
            $this->otp->send($email, EmailOtp::PURPOSE_RESET);
        }

        return back()->with('success', __('A new code has been sent to your email.'));
    }

    public function showResetForm(Request $request)
    {
        if (! $request->session()->has('reset_verified_email')) {
            return redirect()->route('password.request');
        }

        return view('auth.reset-password');
    }

    public function resetPassword(Request $request)
    {
        $email = $request->session()->get('reset_verified_email');
        $user = $email ? User::where('email', $email)->first() : null;
        if (! $user) {
            return redirect()->route('password.request');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'remember_token' => Str::random(60),
        ])->save();

        $request->session()->forget('reset_verified_email');

        return redirect()->route('login')
            ->with('success', __('Password reset successfully. You can now log in with your new password.'));
    }

    public function showChangePassword()
    {
        return view('admin.pages.password.change-password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'min:6'],
            'new_password' => ['required', 'min:8', 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect']);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
