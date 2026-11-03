@extends('admin.layout.app')

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('Applications')])

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                <div>
                    <h5 class="mb-1">{{ $exam->localized('title') }}</h5>
                    <div class="text-muted mb-2">{{ $exam->service->localized('name') }}</div>
                    @include('admin.pages.exams.partials.phase')
                </div>
                @include('admin.pages.exams.partials.period')
                <div class="text-end">
                    <div class="fs-3 fw-semibold" style="color: #003893;">{{ $applications->total() }}</div>
                    <div class="text-muted small">{{ __('Applications') }}</div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.pages.applications.partials.list', [
        'tabUrl' => fn ($key) => route('exams.applications', array_filter([$exam, 'status' => $key])),
        'showExam' => false,
    ])

    <a href="{{ route('exams.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
@endsection
