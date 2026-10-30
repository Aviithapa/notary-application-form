@extends('website.layout.app')

@section('content')

    <div class="npc__auth">
        <div class="npc__auth-card">
            @include('auth.partials.panel')

            <div class="npc__auth-form-wrapper">
                <div class="npc__auth-title">
                    <h2>{{ __('Create an account') }}</h2>
                    <p>{{ __('Register as an applicant. We will email you a code to verify your address.') }}</p>
                </div>

                @include('auth.partials.alerts')

                <form action="{{ route('register.store') }}" method="POST" class="npc__auth-form">
                    @csrf
                    <div class="npc__auth-field">
                        <label for="email">{{ __('Email') }}</label>
                        <div class="npc__auth-input @error('email') has-error @enderror">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" placeholder="johndoe@gmail.com" name="email" id="email"
                                value="{{ old('email') }}" autocomplete="email" required autofocus />
                        </div>
                    </div>

                    <div class="npc__auth-field">
                        <label for="password">{{ __('Password') }}</label>
                        <div class="npc__auth-input @error('password') has-error @enderror">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="••••••••" name="password" id="password"
                                autocomplete="new-password" required minlength="8" />
                            <button type="button" class="npc__auth-toggle" aria-label="{{ __('Show password') }}"
                                data-toggle-password>
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <small class="npc__auth-hint">{{ __('At least 8 characters, with letters and numbers.') }}</small>
                    </div>

                    <div class="npc__auth-field">
                        <label for="password_confirmation">{{ __('Confirm password') }}</label>
                        <div class="npc__auth-input @error('password') has-error @enderror">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="••••••••" name="password_confirmation"
                                id="password_confirmation" autocomplete="new-password" required minlength="8" />
                            <button type="button" class="npc__auth-toggle" aria-label="{{ __('Show password') }}"
                                data-toggle-password>
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button class="npc__auth-submit" type="submit">
                        {{ __('Send verification code') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <p class="npc__auth-switch">
                    {{ __('Already have an account?') }}
                    <a href="{{ url('/login') }}" class="npc__auth-link">{{ __('Log in') }}</a>
                </p>
            </div>
        </div>
    </div>

@endsection

@include('auth.partials.password-toggle-script')
