@extends('admin.layout.app')

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('Exams')])

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <form action="{{ route('exams.index') }}" method="GET" class="d-flex gap-2">
                    <select name="service" class="form-select form-select-sm" style="min-width: 220px;" onchange="this.form.submit()" aria-label="{{ __('Service') }}">
                        <option value="">{{ __('All services') }}</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected($serviceId === $service->id)>{{ $service->localized('name') }}</option>
                        @endforeach
                    </select>
                    <noscript><button class="btn btn-sm btn-light">{{ __('Filter') }}</button></noscript>
                </form>

                @if ($services->isNotEmpty())
                    <a href="{{ route('exams.create', array_filter(['service' => $serviceId])) }}" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> {{ __('Create exam') }}
                    </a>
                @endif
            </div>

            @if ($services->isEmpty())
                <div class="text-center py-5">
                    <i class="bi bi-pencil-square text-muted" style="font-size: 2.5rem;"></i>
                    <h5 class="mt-3">{{ __('No service has exams turned on') }}</h5>
                    <p class="text-muted">{{ __('Turn on "Exam associated" for a service, then create its exams here.') }}</p>
                    <a href="{{ route('services.index') }}" class="btn btn-primary">{{ __('Go to services') }}</a>
                </div>
            @elseif ($exams->isEmpty())
                <div class="text-center text-muted py-5">{{ __('No exams yet.') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Exam') }}</th>
                                <th>{{ __('Application period') }}</th>
                                <th>{{ __('Exam date') }}</th>
                                <th class="text-center">{{ __('Applications') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($exams as $exam)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $exam->localized('title') }}</div>
                                        <div class="text-muted small">{{ $exam->service->localized('name') }}</div>
                                    </td>
                                    <td class="text-nowrap">@include('admin.pages.exams.partials.period')</td>
                                    <td class="text-nowrap small">
                                        @if ($exam->exam_date_bs)
                                            {{ $exam->exam_date_bs }} {{ __('B.S.') }}
                                            <div class="text-muted">{{ $exam->exam_date_ad }}
                                                @if ($exam->start_time) · {{ substr($exam->start_time, 0, 5) }} @endif
                                            </div>
                                        @else
                                            <span class="text-muted">{{ __('Not set') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('exams.applications', $exam) }}" class="fw-semibold">{{ $exam->applications_count }}</a>
                                    </td>
                                    <td>@include('admin.pages.exams.partials.phase')</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('exams.applications', $exam) }}" class="btn btn-sm btn-light" title="{{ __('Applications') }}"><i class="bi bi-people"></i></a>
                                        <a href="{{ route('exams.edit', $exam) }}" class="btn btn-sm btn-light" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm(@js(__('Delete this exam?')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $exams->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
@endsection
