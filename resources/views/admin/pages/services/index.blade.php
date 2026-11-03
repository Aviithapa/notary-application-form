@extends('admin.layout.app')

@php
    $fee = fn ($service) => $service->is_fee_based
        ? __('Rs.') . ' ' . number_format((float) $service->fee_amount, 2)
        : __('Free');
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('Services')])

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <p class="text-muted mb-0">{{ __('Services that applicants can apply for, with their fee and exam settings.') }}</p>
        <div class="d-flex gap-2">
            <div class="btn-group" role="group" aria-label="{{ __('View') }}">
                <a href="{{ route('services.index') }}" class="btn btn-sm {{ $view === 'cards' ? 'btn-primary' : 'btn-light' }}"
                    title="{{ __('Card view') }}" @if ($view === 'cards') aria-current="page" @endif>
                    <i class="bi bi-grid-3x2-gap"></i> <span class="d-none d-sm-inline">{{ __('Cards') }}</span>
                </a>
                <a href="{{ route('services.index', ['view' => 'list']) }}" class="btn btn-sm {{ $view === 'list' ? 'btn-primary' : 'btn-light' }}"
                    title="{{ __('List view') }}" @if ($view === 'list') aria-current="page" @endif>
                    <i class="bi bi-list-ul"></i> <span class="d-none d-sm-inline">{{ __('List') }}</span>
                </a>
            </div>
            <a href="{{ route('services.create') }}" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg"></i> {{ __('Add service') }}
            </a>
        </div>
    </div>

    @if ($services->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-briefcase text-muted" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3">{{ __('No services yet') }}</h5>
                <p class="text-muted">{{ __('Create the first service, for example a new notary licence or a licence renewal.') }}</p>
                <a href="{{ route('services.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> {{ __('Add service') }}</a>
            </div>
        </div>
    @elseif ($view === 'cards')
        <div class="row g-3">
            @foreach ($services as $service)
                <div class="col-md-6 col-xxl-4">
                    <div class="card h-100 mb-0 service-card {{ $service->is_active ? '' : 'opacity-75' }}">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <span class="service-card-icon"><i class="bi {{ $service->icon }}"></i></span>
                                <span class="badge {{ $service->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                    {{ $service->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </div>

                            <h5 class="mb-0">{{ $service->localized('name') }}</h5>
                            <div class="text-muted small mb-2">{{ app()->getLocale() === 'np' ? $service->name : $service->name_np }}</div>
                            <div class="mb-2"><span class="badge bg-primary-subtle text-primary">{{ $service->purposeLabel() }}</span></div>

                            @if ($description = $service->localized('description'))
                                <p class="text-muted small mb-3">{{ \Illuminate\Support\Str::limit($description, 140) }}</p>
                            @endif

                            <ul class="list-unstyled small mb-3 mt-auto service-card-features">
                                <li>
                                    <i class="bi bi-cash-coin"></i>
                                    <span class="text-muted">{{ __('Fee') }}:</span>
                                    <strong>{{ $fee($service) }}</strong>
                                </li>
                                <li>
                                    <i class="bi bi-pencil-square"></i>
                                    <span class="text-muted">{{ __('Exam') }}:</span>
                                    @if ($service->requires_exam)
                                        <strong>{{ __('Required') }}</strong>
                                        <a href="{{ route('exams.index', ['service' => $service->id]) }}" class="ms-1">
                                            ({{ trans_choice(':count exam|:count exams', $service->exams_count) }})
                                        </a>
                                    @else
                                        <strong>{{ __('Not required') }}</strong>
                                    @endif
                                </li>
                                <li>
                                    <i class="bi bi-person-vcard"></i>
                                    <span class="text-muted">{{ __('Application form') }}:</span>
                                    <strong>{{ $service->requires_application_form ? __('Required') : __('Not required') }}</strong>
                                </li>
                            </ul>

                            <div class="d-flex flex-wrap gap-2 border-top pt-3">
                                <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-light">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <a href="{{ route('exams.create', ['service' => $service->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-lg"></i> {{ $service->requires_exam ? __('Create exam') : __('Open applications') }}
                                </a>
                                @include('admin.pages.services.partials.delete', ['service' => $service, 'class' => 'ms-auto'])
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Service') }}</th>
                                <th>{{ __('Purpose') }}</th>
                                <th>{{ __('Fee') }}</th>
                                <th>{{ __('Exam') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($services as $service)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi {{ $service->icon }} fs-4" style="color: #003893;"></i>
                                            <div>
                                                <div class="fw-semibold">{{ $service->localized('name') }}</div>
                                                <div class="text-muted small">{{ app()->getLocale() === 'np' ? $service->name : $service->name_np }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $service->purposeLabel() }}</td>
                                    <td class="text-nowrap">{{ $fee($service) }}</td>
                                    <td>
                                        @if ($service->requires_exam)
                                            <a href="{{ route('exams.index', ['service' => $service->id]) }}">{{ trans_choice(':count exam|:count exams', $service->exams_count) }}</a>
                                        @else
                                            <span class="text-muted">{{ __('Not required') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $service->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                            {{ $service->is_active ? __('Active') : __('Inactive') }}
                                        </span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('services.edit', $service) }}" class="btn btn-sm btn-light" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('exams.create', ['service' => $service->id]) }}" class="btn btn-sm btn-outline-primary"
                                            title="{{ $service->requires_exam ? __('Create exam') : __('Open applications') }}"><i class="bi bi-plus-lg"></i></a>
                                        @include('admin.pages.services.partials.delete', ['service' => $service, 'compact' => true])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .service-card { transition: box-shadow .15s ease, transform .15s ease; }
        .service-card:hover { box-shadow: 0 .5rem 1.25rem rgba(0, 56, 147, .12); transform: translateY(-2px); }
        .service-card-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 48px; height: 48px; border-radius: 12px;
            background: rgba(0, 56, 147, .08); color: #003893; font-size: 1.5rem;
        }
        .service-card-features li { display: flex; align-items: center; gap: .4rem; padding: .2rem 0; }
        .service-card-features i { color: #003893; }
    </style>
@endpush
