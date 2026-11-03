{{-- Top bar shared by every sidebar: logo (phones), menu toggle, council name, account dropdown. --}}
@php $logo = asset('frontend/images/logo.png'); @endphp

<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-1">

            <!-- Brand logo for small screens, where the sidebar (and its logo) is hidden -->
            <a href="{{ url('dashboard') }}" class="d-lg-none me-1">
                <img src="{{ $logo }}" alt="Nepal Notary Public Council" style="height: 40px; width: auto;">
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="button-toggle-menu">
                <i class="bi bi-list fs-22"></i>
            </button>

            <span class="d-none d-md-inline fw-semibold ms-2" style="color: #003893;">{{ __('Nepal Notary Public Council') }}</span>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">
            <!-- Language switch -->
            <li>
                <div class="btn-group btn-group-sm topbar-lang" role="group" aria-label="{{ __('Language') }}">
                    @foreach (config('app.available_locales') as $code => $label)
                        <a href="{{ route('lang.switch', $code) }}" lang="{{ $code === 'np' ? 'ne' : $code }}"
                            class="btn {{ app()->getLocale() === $code ? 'active' : '' }}"
                            @if (app()->getLocale() === $code) aria-current="true" @endif>
                            {{ $code === 'en' ? 'EN' : $label }}
                        </a>
                    @endforeach
                </div>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white fw-semibold"
                            style="width: 32px; height: 32px; background-color: #003893;">
                            {{ strtoupper(substr(Auth::user()->email, 0, 1)) }}
                        </span>
                    </span>
                    <span class="d-lg-block d-none">
                        <h5 class="my-0 fw-normal">{{ Auth::user()->username ?: Auth::user()->email }}
                            <i class="bi bi-chevron-down d-none d-sm-inline-block align-middle"></i>
                        </h5>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ __('Welcome!') }}</h6>
                        <small class="text-muted text-overflow d-block">{{ Auth::user()->email }}</small>
                    </div>

                    @if (in_array(Auth::user()->mainRole()?->name, [\App\Enums\RoleEnum::ADMIN, \App\Enums\RoleEnum::SUPER_ADMIN], true))
                        <a href="{{ route('change.password') }}" class="dropdown-item">
                            <i class="bi bi-key fs-18 align-middle me-1"></i>
                            <span>{{ __('Change password') }}</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right fs-18 align-middle me-1"></i>
                            <span>{{ __('Logout') }}</span>
                        </button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
