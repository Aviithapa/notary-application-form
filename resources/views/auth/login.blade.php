@extends('website.layout.app')

@section('content')

    <div class="npc__auth">
        <div class="npc__auth-card">
            @include('auth.partials.panel')

            <div class="npc__auth-form-wrapper">
                <div class="npc__auth-title">
                    <h2>{{ __('Welcome back') }}</h2>
                    <p>{{ __('Sign in to continue to your dashboard') }}</p>
                </div>

                @include('auth.partials.alerts')

                <form action="{{ url('/login') }}" method="POST" class="npc__auth-form">
                    @csrf
                    <div class="npc__auth-field">
                        <label for="email">{{ __('Email') }}</label>
                        <div class="npc__auth-input @error('email') has-error @enderror">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="text" placeholder="johndoe@gmail.com" name="email" id="email"
                                value="{{ old('email') }}" autocomplete="username" required autofocus />
                        </div>
                    </div>

                    <div class="npc__auth-field">
                        <label for="password">{{ __('Password') }}</label>
                        <div class="npc__auth-input @error('password') has-error @enderror">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" placeholder="••••••••" name="password" id="password"
                                autocomplete="current-password" required />
                            <button type="button" class="npc__auth-toggle" aria-label="{{ __('Show password') }}"
                                data-toggle-password>
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="npc__auth-row">
                        <label class="npc__auth-remember">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                            <span>{{ __('Remember me') }}</span>
                        </label>
                        <a href="{{ route('password.request') }}" class="npc__auth-link">{{ __('Forgot password?') }}</a>
                    </div>

                    <button class="npc__auth-submit" type="submit">
                        {{ __('Log in') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>

                <p class="npc__auth-switch">
                    {{ __("Don't have an account?") }}
                    <a href="{{ route('register') }}" class="npc__auth-link">{{ __('Register') }}</a>
                </p>
            </div>
        </div>
    </div>

@endsection

@include('auth.partials.password-toggle-script')
