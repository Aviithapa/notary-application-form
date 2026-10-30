@extends('website.layout.app')

@section('content')

    <div class="npc__auth">
        <div class="npc__auth-card">
            @include('auth.partials.panel')

            <div class="npc__auth-form-wrapper">
                <div class="npc__auth-title">
                    <h2>{{ __('Forgot password?') }}</h2>
                    <p>{{ __('Enter your account email and we will send you a code to reset your password.') }}</p>
                </div>

                @include('auth.partials.alerts')

                <form action="{{ route('password.email') }}" method="POST" class="npc__auth-form">
                    @csrf
                    <div class="npc__auth-field">
                        <label for="email">{{ __('Email') }}</label>
                        <div class="npc__auth-input @error('email') has-error @enderror">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" placeholder="johndoe@gmail.com" name="email" id="email"
                                value="{{ old('email') }}" autocomplete="email" required autofocus />
                        </div>
                    </div>

                    <button class="npc__auth-submit" type="submit">
                        {{ __('Send reset code') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <p class="npc__auth-switch">
                    <a href="{{ url('/login') }}" class="npc__auth-link"><i class="fa-solid fa-arrow-left"></i> {{ __('Back to login') }}</a>
                </p>
            </div>
        </div>
    </div>

@endsection
