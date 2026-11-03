@include('admin.sidebar.partials.topbar')

<div class="leftside-menu">

    @include('admin.sidebar.partials.brand')

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

            @if (Auth::user()->mainRole()?->name === \App\Enums\RoleEnum::APPLICANT)
                <li class="side-nav-title">{{ __('Application') }}</li>

                <li class="side-nav-item {{ request()->routeIs('applicant.personal*') ? 'menuitem-active' : '' }}">
                    <a href="{{ route('applicant.personal') }}" class="side-nav-link {{ request()->routeIs('applicant.personal*') ? 'active' : '' }}">
                        <i class="bi bi-person-vcard"></i>
                        <span> {{ __('Personal details') }} </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('applicant.qualifications*') ? 'menuitem-active' : '' }}">
                    <a href="{{ route('applicant.qualifications') }}" class="side-nav-link {{ request()->routeIs('applicant.qualifications*') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard"></i>
                        <span> {{ __('Qualifications') }} </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('applicant.trainings*') ? 'menuitem-active' : '' }}">
                    <a href="{{ route('applicant.trainings') }}" class="side-nav-link {{ request()->routeIs('applicant.trainings*') ? 'active' : '' }}">
                        <i class="bi bi-award"></i>
                        <span> {{ __('Trainings') }} </span>
                    </a>
                </li>

                <li class="side-nav-title">{{ __('Exams') }}</li>

                <li class="side-nav-item {{ request()->routeIs('applicant.exams*') ? 'menuitem-active' : '' }}">
                    <a href="{{ route('applicant.exams') }}" class="side-nav-link {{ request()->routeIs('applicant.exams*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-check"></i>
                        <span> {{ __('Apply for exam') }} </span>
                    </a>
                </li>
                <li class="side-nav-item {{ request()->routeIs('applicant.applications') ? 'menuitem-active' : '' }}">
                    <a href="{{ route('applicant.applications') }}" class="side-nav-link {{ request()->routeIs('applicant.applications') ? 'active' : '' }}">
                        <i class="bi bi-inbox"></i>
                        <span> {{ __('My applications') }} </span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
