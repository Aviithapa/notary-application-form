@extends('admin.layout.app')

@php
    $steps = [
        'personal' => ['label' => __('Personal details'), 'icon' => 'bi-person-vcard'],
        'qualifications' => ['label' => __('Qualifications'), 'icon' => 'bi-mortarboard'],
        'trainings' => ['label' => __('Trainings'), 'icon' => 'bi-award'],
    ];
    $done = count(array_filter($progress));
    // Continue from the first step that has nothing saved yet
    $nextStep = array_search(false, $progress, true) ?: 'personal';
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __('Welcome!') }}</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="card-title mb-1">{{ __('Your application') }}</h5>
                            <p class="text-muted mb-0">
                                {{ __('Complete the three steps below. You can save each step and come back later.') }}
                            </p>
                        </div>
                        <span class="badge {{ $done === 3 ? 'bg-success' : 'bg-warning text-dark' }} fs-6">{{ $done }}/3</span>
                    </div>

                    <div class="progress mb-3" style="height: 6px;" role="progressbar" aria-valuenow="{{ $done }}" aria-valuemin="0" aria-valuemax="3">
                        <div class="progress-bar" style="width: {{ round($done / 3 * 100) }}%; background-color: #003893;"></div>
                    </div>

                    <div class="list-group mb-3">
                        @foreach ($steps as $key => $step)
                            <a href="{{ route('applicant.' . $key) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                <i class="bi {{ $step['icon'] }} fs-4" style="color: #003893;"></i>
                                <span class="flex-grow-1">{{ $loop->iteration }}. {{ $step['label'] }}</span>
                                @if ($progress[$key])
                                    <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle-fill me-1"></i>{{ __('Saved') }}</span>
                                @else
                                    <span class="badge bg-light text-muted">{{ __('Not started') }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>

                    <a href="{{ route('applicant.' . $nextStep) }}" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                        {{ $done === 0 ? __('Start application') : ($done === 3 ? __('Review application') : __('Continue application')) }}
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ __('Exams') }}</h5>
                    @if ($openExams > 0)
                        <p class="mb-3">{{ trans_choice(':count exam is open for applications.|:count exams are open for applications.', $openExams) }}</p>
                        <a href="{{ route('applicant.exams') }}" class="btn btn-sm btn-success"><i class="bi bi-calendar-check me-1"></i> {{ __('Apply for exam') }}</a>
                    @else
                        <p class="text-muted mb-3">{{ __('No exams are taking applications right now.') }}</p>
                    @endif
                    @if ($applicationsCount > 0)
                        <a href="{{ route('applicant.applications') }}" class="btn btn-sm btn-light">{{ __('My applications') }} ({{ $applicationsCount }})</a>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-2">{{ __('Account') }}</h5>
                    <p class="text-muted mb-2">
                        {{ __('You are signed in as') }} <strong class="text-break">{{ $user->email }}</strong>.
                    </p>
                    <span class="badge bg-success-subtle text-success">{{ __('Applicant') }}</span>
                </div>
            </div>
        </div>
    </div>
@endsection
