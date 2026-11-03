@extends('admin.layout.app')

@php
    $today = \App\Models\Exam::today();
    $money = fn ($amount) => $amount === null ? __('Free') : __('Rs.') . ' ' . number_format((float) $amount, 2);
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('Apply for exam')])

    @unless ($formComplete)
        <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2">
            <i class="bi bi-exclamation-triangle"></i>
            <span>{{ __('Complete your personal details and qualifications before applying.') }}</span>
            <a href="{{ route('applicant.personal') }}" class="alert-link ms-auto">{{ __('Complete application form') }} →</a>
        </div>
    @endunless

    <h5 class="mb-3">{{ __('Open for applications') }}</h5>

    @if ($open->isEmpty())
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-calendar-x" style="font-size: 2.25rem;"></i>
                <p class="mt-2 mb-0">{{ __('No exams are taking applications right now.') }}</p>
            </div>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach ($open as $exam)
                @php
                    $applied = in_array($exam->id, $appliedExamIds, true);
                    $doubleFee = $exam->phase() === \App\Models\Exam::PHASE_DOUBLE_FEE;
                    $deadline = $doubleFee ? $exam->double_fee_end_ad : $exam->application_end_ad;
                    $deadlineBs = $doubleFee ? $exam->double_fee_end_bs : $exam->application_end_bs;
                    $daysLeft = (int) \Illuminate\Support\Carbon::parse($today)->diffInDays($deadline);
                    $blocked = $exam->service->requires_application_form && ! $formComplete;
                @endphp
                <div class="col-md-6 col-xxl-4">
                    <div class="card h-100 mb-0 exam-card {{ $doubleFee ? 'exam-card--double' : '' }}">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                                <span class="exam-card-icon"><i class="bi {{ $exam->service->icon }}"></i></span>
                                @include('admin.pages.exams.partials.phase')
                            </div>

                            <h5 class="mb-0">{{ $exam->localized('title') }}</h5>
                            <div class="text-muted small mb-3">{{ $exam->service->localized('name') }}</div>

                            <ul class="list-unstyled small mb-3 exam-card-facts">
                                <li><i class="bi bi-calendar-event"></i>
                                    <span class="text-muted">{{ __('Last date') }}:</span>
                                    <strong>{{ $deadlineBs }}</strong> <span class="text-muted">({{ $deadline }})</span>
                                </li>
                                <li><i class="bi bi-hourglass-split"></i>
                                    <strong class="{{ $daysLeft <= 3 ? 'text-danger' : '' }}">
                                        {{ $daysLeft === 0 ? __('Last day today') : trans_choice(':count day left|:count days left', $daysLeft) }}
                                    </strong>
                                </li>
                                <li><i class="bi bi-cash-coin"></i>
                                    <span class="text-muted">{{ __('Fee') }}:</span>
                                    <strong>{{ $money($exam->currentFee()) }}</strong>
                                    @if ($doubleFee) <span class="badge bg-warning-subtle text-warning">{{ __('Double fee') }}</span> @endif
                                </li>
                                @if (! $doubleFee && $exam->double_fee_end_ad && $exam->service->is_fee_based)
                                    <li class="text-muted ps-4">
                                        {{ __('After :date the fee doubles until :until.', ['date' => $exam->application_end_bs, 'until' => $exam->double_fee_end_bs]) }}
                                    </li>
                                @endif
                                @if ($exam->exam_date_bs)
                                    <li><i class="bi bi-pencil-square"></i>
                                        <span class="text-muted">{{ __('Exam date') }}:</span> {{ $exam->exam_date_bs }}
                                        @if ($exam->start_time) · {{ substr($exam->start_time, 0, 5) }} @endif
                                    </li>
                                @endif
                                @if ($venue = $exam->localized('venue'))
                                    <li><i class="bi bi-geo-alt"></i> {{ $venue }}</li>
                                @endif
                            </ul>

                            <div class="mt-auto border-top pt-3">
                                @if ($applied)
                                    <a href="{{ route('applicant.applications') }}" class="btn btn-sm btn-success w-100">
                                        <i class="bi bi-check2-circle me-1"></i> {{ __('Applied') }}
                                    </a>
                                @elseif ($blocked)
                                    <a href="{{ route('applicant.personal') }}" class="btn btn-sm btn-outline-secondary w-100">
                                        {{ __('Complete application form to apply') }}
                                    </a>
                                @else
                                    <a href="{{ route('applicant.exams.apply.form', $exam) }}" class="btn btn-sm btn-primary w-100" style="background-color: #003893; border-color: #003893;">
                                        {{ __('Apply now') }} <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($upcoming->isNotEmpty())
        <h5 class="mb-3">{{ __('Opening soon') }}</h5>
        <div class="row g-3">
            @foreach ($upcoming as $exam)
                <div class="col-md-6 col-xxl-4">
                    <div class="card h-100 mb-0">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <h6 class="mb-0">{{ $exam->localized('title') }}</h6>
                                @include('admin.pages.exams.partials.phase')
                            </div>
                            <div class="text-muted small mb-2">{{ $exam->service->localized('name') }}</div>
                            @include('admin.pages.exams.partials.period')
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection

@push('styles')
    <style>
        .exam-card { border-top: 3px solid #003893; }
        .exam-card--double { border-top-color: #f5a623; }
        .exam-card-icon {
            display: inline-flex; align-items: center; justify-content: center;
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(0, 56, 147, .08); color: #003893; font-size: 1.35rem;
        }
        .exam-card-facts li { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem; padding: .2rem 0; }
        .exam-card-facts i { color: #003893; }
    </style>
@endpush
