{{-- Shared by registration and password reset: $title, $email, $action and $resendAction come from the controller --}}
@extends('website.layout.app')

@section('content')

    <div class="npc__auth">
        <div class="npc__auth-card">
            @include('auth.partials.panel')

            <div class="npc__auth-form-wrapper">
                <div class="npc__auth-title">
                    <h2>{{ $title }}</h2>
                    <p>{{ __('Enter the 6-digit code we sent to') }} <strong>{{ $email }}</strong></p>
                </div>

                @include('auth.partials.alerts')

                <form action="{{ $action }}" method="POST" class="npc__auth-form">
                    @csrf
                    <div class="npc__auth-field">
                        <label for="otp">{{ __('Verification code') }}</label>
                        <div class="npc__auth-input @error('otp') has-error @enderror">
                            <i class="fa-solid fa-key"></i>
                            <input type="text" name="otp" id="otp" class="npc__auth-otp" inputmode="numeric"
                                pattern="[0-9]{6}" maxlength="6" placeholder="••••••" autocomplete="one-time-code"
                                required autofocus />
                        </div>
                        <small class="npc__auth-hint">{{ __('The code expires in 10 minutes.') }}</small>
                    </div>

                    <button class="npc__auth-submit" type="submit">
                        {{ __('Verify') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <form action="{{ $resendAction }}" method="POST" class="npc__auth-switch">
                    @csrf
                    {{ __("Didn't get the code?") }}
                    <button type="submit" class="npc__auth-link npc__auth-link-button">{{ __('Resend code') }}</button>
                </form>
            </div>
        </div>
    </div>

@endsection
