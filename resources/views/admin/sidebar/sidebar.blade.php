{{--
    Admin / super admin sidebar. Only links to routes that exist; add new admin pages here.
    Items may carry a 'badge' count (shown when non-zero), used for pending applications per service.
--}}
@php
    use App\Models\ExamApplication;
    use App\Models\Service;

    // Applications per service, with the number still waiting for review (pending)
    $applicationServices = Service::whereHas('exams')
        ->ordered()
        ->withCount([
            'applications as pending_count' => fn($query) => $query->where(
                'exam_applications.status',
                ExamApplication::PENDING,
            ),
        ])
        ->get();
    $onApplications = request()->routeIs('applications.index');
    $currentService = request()->integer('service') ?: null;

    $applicationItems = [
        [
            'label' => __('All services'),
            'icon' => 'bi-inbox',
            'url' => route('applications.index'),
            'active' => $onApplications && !$currentService,
            'badge' => $applicationServices->sum('pending_count'),
        ],
    ];
    foreach ($applicationServices as $service) {
        $applicationItems[] = [
            'label' => $service->localized('name'),
            'icon' => $service->icon,
            'url' => route('applications.index', ['service' => $service->id]),
            'active' => $onApplications && $currentService === $service->id,
            'badge' => $service->pending_count,
        ];
    }

    $menu = [
        __('Main') => [
            [
                'label' => __('Dashboard'),
                'icon' => 'bi-speedometer2',
                'url' => route('dashboard'),
                'active' => request()->routeIs('dashboard'),
            ],
        ],
        __('Applications') => $applicationItems,
        __('Service Management') => [
            [
                'label' => __('Services'),
                'icon' => 'bi-grid-3x2-gap',
                'url' => route('services.index'),
                'active' => request()->routeIs('services.*'),
            ],
            [
                'label' => __('Exams'),
                'icon' => 'bi-pencil-square',
                'url' => route('exams.index'),
                'active' => request()->routeIs('exams.*'),
            ],
        ],
        __('Management') => [
            [
                'label' => __('User Management'),
                'icon' => 'bi-people',
                'url' => route('user.index'),
                'active' => request()->routeIs('user.*'),
            ],
        ],
        __('Account') => [
            [
                'label' => __('Change password'),
                'icon' => 'bi-key',
                'url' => route('change.password'),
                'active' => request()->routeIs('change.password'),
            ],
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
                            @if (!empty($item['badge']))
                                <span class="badge bg-warning text-dark rounded-pill float-end"
                                    title="{{ __('Pending') }}">{{ $item['badge'] }}</span>
                            @endif
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
