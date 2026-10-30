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

                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bi bi-box-arrow-right fs-18 align-middle me-1"></i>
                            <span>{{ __('Logout') }}</span>
                        </button>
                    </form>
                </div>
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="d-none d-sm-inline">{{ __('Logout') }}</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

<div class="leftside-menu">

    <!-- Brand Logo -->
    <a href="{{ url('dashboard') }}" class="logo logo-dark" style="height: auto; padding: 16px 0;">
        <span class="logo-lg">
            <img src="{{ $logo }}" alt="Nepal Notary Public Council" style="height: 90px; width: auto;">
        </span>
        <span class="logo-sm">
            <img src="{{ $logo }}" alt="Nepal Notary Public Council" style="height: 40px; width: auto;">
        </span>
    </a>

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav">

            <li class="side-nav-title">{{ __('Main') }}</li>

            <li class="side-nav-item">
                <a href="{{ url('dashboard') }}" class="side-nav-link">
                    <i class="bi bi-speedometer2"></i>
                    <span> {{ __('Dashboard') }} </span>
                </a>
            </li>

            <li class="side-nav-title">{{ __('Account') }}</li>

            <li class="side-nav-item">
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="side-nav-link w-100 border-0 bg-transparent text-start">
                        <i class="bi bi-box-arrow-right"></i>
                        <span> {{ __('Logout') }} </span>
                    </button>
                </form>
            </li>
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
