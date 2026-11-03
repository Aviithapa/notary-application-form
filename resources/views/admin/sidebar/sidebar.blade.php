{{-- Admin / super admin sidebar. Only links to routes that exist; add new admin pages here. --}}
@php
    $menu = [
        __('Main') => [
            ['label' => __('Dashboard'), 'icon' => 'bi-speedometer2', 'url' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ],
        __('Service Management') => [
            ['label' => __('Services'), 'icon' => 'bi-grid-3x2-gap', 'url' => route('services.index'), 'active' => request()->routeIs('services.*')],
            ['label' => __('Exams'), 'icon' => 'bi-pencil-square', 'url' => route('exams.index'), 'active' => request()->routeIs('exams.*')],
        ],
        __('Management') => [
            ['label' => __('User Management'), 'icon' => 'bi-people', 'url' => route('user.index'), 'active' => request()->routeIs('user.*')],
        ],
        __('Account') => [
            ['label' => __('Change password'), 'icon' => 'bi-key', 'url' => route('change.password'), 'active' => request()->routeIs('change.password')],
        ],
    ];
@endphp

@include('admin.sidebar.partials.topbar')

<div class="leftside-menu">

    @include('admin.sidebar.partials.brand')

    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <ul class="side-nav">
            @foreach ($menu as $section => $items)
                <li class="side-nav-title">{{ $section }}</li>

                @foreach ($items as $item)
                    <li class="side-nav-item {{ $item['active'] ? 'menuitem-active' : '' }}">
                        <a href="{{ $item['url'] }}" class="side-nav-link {{ $item['active'] ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span> {{ $item['label'] }} </span>
                        </a>
                    </li>
                @endforeach
            @endforeach
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- ========== Left Sidebar End ========== -->
