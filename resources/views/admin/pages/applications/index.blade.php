@extends('admin.layout.app')

{{-- Applications across exams; the sidebar links here per service (?service=). --}}
@php
    $query = fn (array $changes = []) => array_filter(
        array_merge(['service' => $service?->id, 'exam' => $examId, 'status' => $status], $changes),
        fn ($value) => $value !== null && $value !== '',
    );
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => $service ? $service->localized('name') : __('All applications')])

    <div class="card">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="applications-icon"><i class="bi {{ $service?->icon ?? 'bi-inbox' }}"></i></span>
                <div>
                    <h5 class="mb-0">{{ $service ? $service->localized('name') : __('All applications') }}</h5>
                    <div class="text-muted small">
                        {{ trans_choice(':count application|:count applications', $statusCounts->sum()) }}
                        · <span class="text-warning fw-semibold">{{ $statusCounts[\App\Models\ExamApplication::PENDING] ?? 0 }} {{ __('Pending') }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('applications.index') }}" method="GET" class="d-flex gap-2">
                @if ($service) <input type="hidden" name="service" value="{{ $service->id }}"> @endif
                @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                <select name="exam" class="form-select form-select-sm" style="min-width: 240px;" onchange="this.form.submit()" aria-label="{{ __('Exam') }}">
                    <option value="">{{ __('All exams') }}</option>
                    @foreach ($exams as $exam)
                        <option value="{{ $exam->id }}" @selected($examId === $exam->id)>
                            {{ $exam->localized('title') }}@unless ($service) ({{ $exam->service->localized('name') }})@endunless
                        </option>
                    @endforeach
                </select>
                <noscript><button class="btn btn-sm btn-light">{{ __('Filter') }}</button></noscript>
            </form>
        </div>
    </div>

    @include('admin.pages.applications.partials.list', [
        'tabUrl' => fn ($key) => route('applications.index', $query(['status' => $key])),
        'showExam' => true,
        'showService' => ! $service,
    ])
@endsection

@push('styles')
    <style>
        .applications-icon {
            display: inline-flex; align-items: center; justify-content: center; flex: none;
            width: 44px; height: 44px; border-radius: 12px; background: rgba(0, 56, 147, .08); color: #003893; font-size: 1.35rem;
        }
    </style>
@endpush
