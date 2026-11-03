@extends('admin.layout.app')

@php
    $editing = $exam->exists;
    $v = fn (string $field) => old($field, $exam->{$field});
    $field = 'admin.pages.applicant.partials.input';
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => $editing ? __('Edit exam') : __('Create exam')])

    @if ($services->isEmpty())
        <div class="alert alert-warning">
            {{ __('Turn on "Exam associated" for a service, then create its exams here.') }}
            <a href="{{ route('services.index') }}" class="alert-link">{{ __('Go to services') }}</a>
        </div>
    @endif

    <form action="{{ $editing ? route('exams.update', $exam) : route('exams.store') }}" method="POST" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('Exam details') }}</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">{{ __('Service') }} *</label>
                        <select class="form-select @error('service_id') is-invalid @enderror" id="service_id" name="service_id">
                            <option value="">{{ __('Select service') }}</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((string) $v('service_id') === (string) $service->id)>{{ $service->localized('name') }}</option>
                            @endforeach
                        </select>
                        @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="exam_status">{{ __('Status') }} *</label>
                        <select class="form-select @error('status') is-invalid @enderror" id="exam_status" name="status">
                            @foreach (\App\Models\Exam::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($v('status') === $key)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Applicants can apply only while the exam is published and within the application period.') }}</div>
                    </div>

                    @include($field, ['name' => 'title', 'label' => __('Exam title (English)') . ' *', 'value' => $v('title')])
                    @include($field, ['name' => 'title_np', 'label' => __('Exam title (Nepali)'), 'value' => $v('title_np'), 'lang' => 'ne'])
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-1">{{ __('Application period') }}</h5>
                <p class="text-muted small mb-3">
                    {{ __('Applicants pay the normal fee until the end date. After that they can still apply until the double fee date, at twice the fee.') }}
                </p>
                <div class="row g-3">
                    @foreach ([
                        'application_start' => __('Application start date') . ' *',
                        'application_end' => __('Application end date (normal fee)') . ' *',
                        'double_fee_end' => __('Double fee last date (दोब्बर दस्तुर)'),
                    ] as $date => $label)
                        @include('admin.pages.applicant.partials.date-pair', [
                            'label' => $label,
                            'idPrefix' => $date,
                            'bsName' => "{$date}_bs", 'bsKey' => "{$date}_bs", 'bsValue' => $v("{$date}_bs"),
                            'adName' => "{$date}_ad", 'adKey' => "{$date}_ad", 'adValue' => $v("{$date}_ad"),
                        ])
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">{{ __('Exam schedule') }}</h5>
                <div class="row g-3">
                    @include('admin.pages.applicant.partials.date-pair', [
                        'label' => __('Exam date'),
                        'idPrefix' => 'exam_date',
                        'bsName' => 'exam_date_bs', 'bsKey' => 'exam_date_bs', 'bsValue' => $v('exam_date_bs'),
                        'adName' => 'exam_date_ad', 'adKey' => 'exam_date_ad', 'adValue' => $v('exam_date_ad'),
                    ])

                    @include($field, ['name' => 'start_time', 'label' => __('Start time'), 'value' => old('start_time', $exam->start_time ? substr($exam->start_time, 0, 5) : null), 'type' => 'time'])
                    @include($field, ['name' => 'duration_minutes', 'label' => __('Duration (minutes)'), 'value' => $v('duration_minutes'), 'type' => 'number', 'placeholder' => __('e.g. 180')])

                    @include($field, ['name' => 'venue', 'label' => __('Venue (English)'), 'value' => $v('venue')])
                    @include($field, ['name' => 'venue_np', 'label' => __('Venue (Nepali)'), 'value' => $v('venue_np'), 'lang' => 'ne'])

                    @include($field, ['name' => 'full_marks', 'label' => __('Full marks'), 'value' => $v('full_marks'), 'type' => 'number', 'placeholder' => __('e.g. 100')])
                    @include($field, ['name' => 'pass_marks', 'label' => __('Pass marks'), 'value' => $v('pass_marks'), 'type' => 'number', 'placeholder' => __('e.g. 40')])
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('exams.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
            <button type="submit" class="btn btn-primary" style="background-color: #003893; border-color: #003893;" @disabled($services->isEmpty())>
                <i class="bi bi-check2 me-1"></i> {{ $editing ? __('Save changes') : __('Create exam') }}
            </button>
        </div>
    </form>
@endsection

@include('admin.pages.applicant.partials.scripts')
