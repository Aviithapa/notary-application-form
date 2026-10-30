@extends('website.layout.app')

@section('content')

    <div class="npc__auth">
        <div class="npc__auth-card">
            <div class="npc__auth-panel">
                <div class="npc__auth-panel-content">
                    <div class="npc__auth-flag">
                        <img src="{{ asset('frontend/images/np_flag.gif') }}" alt="flag of nepal" />
                    </div>
                    <h3>{{ __('Notary Public Council') }}</h3>
                    <p>{{ __('Secure access to the council dashboard for administrators and registered users.') }}</p>
                    <ul class="npc__auth-features">
                        <li><i class="fa-solid fa-shield-halved"></i> {{ __('Secure sign in') }}</li>
                        <li><i class="fa-solid fa-file-signature"></i> {{ __('Manage applications') }}</li>
                        <li><i class="fa-solid fa-bell"></i> {{ __('Track notices and updates') }}</li>
                    </ul>
                </div>
            </div>

            <div class="npc__auth-form-wrapper">
                <div class="npc__auth-logo">
                    {{-- <img src="{{ getSiteSetting('logo_image') }}" alt="npc logo" /> --}}
                </div>
                <div class="npc__auth-title">
                    <h2>{{ __('Welcome back') }}</h2>
                    <p>{{ __('Sign in to continue to your dashboard') }}</p>
                </div>

                @if ($errors->any())
                    <div class="npc__auth-alert" role="alert">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                {{-- 'active' error carries a server-generated verification link --}}
                                <p>{!! $error === $errors->first('active') ? $error : e($error) !!}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form action="{{ url('/login') }}" method="POST" class="npc__auth-form">
                    @csrf
                    <div class="npc__auth-field">
                        <label for="email">{{ __('Email') }}</label>
                        <div class="npc__auth-input @error('email') has-error @enderror">
                            {{-- <i class="fa-regular fa-envelope"></i> --}}
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

                    <label class="npc__auth-remember">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} />
                        <span>{{ __('Remember me') }}</span>
                    </label>

                    <button class="npc__auth-submit" type="submit">
                        {{ __('Log in') }}
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = btn.parentElement.querySelector('input');
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
            });
        });
    </script>
@endpush
