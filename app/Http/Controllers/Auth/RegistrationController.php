<?php

namespace App\Http\Controllers\Auth;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegistrationController extends Controller
{
    public function __construct(protected EmailOtp $otp)
    {
    }

    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'status' => UserStatusEnum::INACTIVE->value,
            ]);
            $user->roles()->attach(Role::where('name', RoleEnum::APPLICANT)->firstOrFail());

            return $user;
        });

        $this->otp->send($user->email, EmailOtp::PURPOSE_REGISTER);
        $request->session()->put('register_email', $user->email);

        return redirect()->route('register.verify')
            ->with('success', __('We have sent a verification code to your email.'));
    }

    public function verifyOtpIndex(Request $request)
    {
        $email = $request->session()->get('register_email');
        if (! $email) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', [
            'email' => $email,
            'action' => route('register.verify.submit'),
            'resendAction' => route('register.resend'),
            'title' => __('Verify your email'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => ['required', 'digits:6']]);

        $email = $request->session()->get('register_email');
        $user = $email ? User::where('email', $email)->first() : null;
        if (! $user) {
            return redirect()->route('register');
        }

        if (! $this->otp->verify($email, EmailOtp::PURPOSE_REGISTER, $request->input('otp'))) {
            return back()->withErrors(['otp' => __('The code is invalid or has expired.')]);
        }

        $user->forceFill([
            'status' => UserStatusEnum::ACTIVE->value,
            'email_verified_at' => now(),
        ])->save();

        $request->session()->forget('register_email');
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->session()->get('register_email');
        if (! $email || ! User::where('email', $email)->exists()) {
            return redirect()->route('register');
        }

        if (! $this->otp->canResend($email, EmailOtp::PURPOSE_REGISTER)) {
            return back()->withErrors(['otp' => __('Please wait a minute before requesting a new code.')]);
        }

        $this->otp->send($email, EmailOtp::PURPOSE_REGISTER);

        return back()->with('success', __('A new code has been sent to your email.'));
    }
}
