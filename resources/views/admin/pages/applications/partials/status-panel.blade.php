{{--
    Where the application stands: workflow steps, the latest remarks, admin actions (when $admin)
    and the status history. Needs $application (statusLogs loaded) and $admin.
--}}
@php
    use App\Models\ExamApplication;

    // Main path shown as steps; rejected / failed are shown on the step where they happened
    // (a service without an exam stops at approved)
    $path = $application->exam->service->requires_exam
        ? [ExamApplication::PENDING, ExamApplication::APPROVED, ExamApplication::READY_FOR_ADMIT_CARD, ExamApplication::ADMIT_CARD_GENERATED, ExamApplication::PASSED]
        : [ExamApplication::PENDING, ExamApplication::APPROVED];
    $current = $application->status;
    $reached = match ($current) {
        ExamApplication::REJECTED => array_search($application->statusLogs->firstWhere('to_status', ExamApplication::REJECTED)?->from_status, $path, true),
        ExamApplication::FAILED => array_search(ExamApplication::PASSED, $path, true),
        default => array_search($current, $path, true),
    };
    $reached = $reached === false ? 0 : $reached;

    $buttonClass = [
        ExamApplication::PENDING => 'btn-outline-secondary',
        ExamApplication::APPROVED => 'btn-info',
        ExamApplication::READY_FOR_ADMIT_CARD => 'btn-primary',
        ExamApplication::ADMIT_CARD_GENERATED => 'btn-dark',
        ExamApplication::PASSED => 'btn-success',
        ExamApplication::FAILED => 'btn-outline-danger',
        ExamApplication::REJECTED => 'btn-danger',
    ];
    $actionLabel = [
        ExamApplication::PENDING => __('Reopen'),
        ExamApplication::APPROVED => __('Approve'),
        ExamApplication::READY_FOR_ADMIT_CARD => __('Mark ready for admit card'),
        ExamApplication::ADMIT_CARD_GENERATED => __('Generate admit card'),
        ExamApplication::PASSED => __('Mark passed'),
        ExamApplication::FAILED => __('Mark failed'),
        ExamApplication::REJECTED => __('Reject'),
    ];
    $tz = config('app.business_timezone');
@endphp

<div class="card">
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 application-view-title">
            <h5 class="m-0"><i class="bi bi-flag me-1"></i> {{ __('Application status') }}</h5>
            @include('admin.pages.applications.partials.status')
        </div>

        {{-- Progress steps --}}
        <ol class="status-steps">
            @foreach ($path as $i => $step)
                @php
                    $isEnd = $i === $reached && in_array($current, [ExamApplication::REJECTED, ExamApplication::FAILED], true);
                    $finished = $i === $reached && $i === array_key_last($path) && ! $isEnd;
                    $state = $isEnd ? 'is-stopped' : ($i < $reached || $finished ? 'is-done' : ($i === $reached ? 'is-current' : ''));
                    $label = $isEnd ? $application->statusLabel() : __(ExamApplication::STATUSES[$step][0]);
                @endphp
                <li class="{{ $state }}">
                    <span class="status-step-dot">
                        @if ($state === 'is-done') <i class="bi bi-check-lg"></i>
                        @elseif ($state === 'is-stopped') <i class="bi bi-x-lg"></i>
                        @else {{ $i + 1 }} @endif
                    </span>
                    <span class="status-step-label">{{ $label }}</span>
                </li>
            @endforeach
        </ol>

        @if ($application->roll_number)
            <p class="mb-2"><i class="bi bi-person-badge me-1"></i> {{ __('Roll no.') }} <strong>{{ $application->rollNumberLabel() }}</strong></p>
        @endif

        @if ($application->remarks)
            <div class="alert {{ $current === ExamApplication::REJECTED ? 'alert-danger' : 'alert-secondary' }} py-2 mb-3">
                <strong>{{ $current === ExamApplication::REJECTED ? __('Reason for rejection') : __('Remarks') }}:</strong>
                {{ $application->remarks }}
            </div>
        @endif

        {{-- Applicant: how to fix and resubmit a rejected application --}}
        @if (! $admin && $current === ExamApplication::REJECTED)
            <div class="border rounded p-3 bg-light no-print">
                @if ($application->canResubmit())
                    <div class="fw-semibold mb-2">{{ __('How to resubmit') }}</div>
                    <ol class="small mb-3 ps-3">
                        <li>{{ __('Read the reason above and fix the problem.') }}
                            {{ __('Change your') }}
                            <a href="{{ route('applicant.personal') }}">{{ __('personal details') }}</a>,
                            <a href="{{ route('applicant.qualifications') }}">{{ __('qualifications') }}</a> {{ __('or') }}
                            <a href="{{ route('applicant.trainings') }}">{{ __('trainings') }}</a> {{ __('if needed.') }}
                        </li>
                        <li>{{ __('Open the resubmit form, replace the voucher, signature or thumbprints if needed, and confirm the declaration.') }}</li>
                        <li>{{ __('Your application goes back to Pending for the council to review again.') }}</li>
                    </ol>
                    <a href="{{ route('applicant.applications.resubmit.form', $application) }}" class="btn btn-warning">
                        <i class="bi bi-arrow-repeat me-1"></i> {{ __('Edit & resubmit') }}
                    </a>
                    <span class="small text-muted ms-2">
                        {{ __('Possible until :date', ['date' => $application->exam->lastApplicationDate() ? \App\Support\NepaliDate::adToBs($application->exam->lastApplicationDate()) . ' ' . __('B.S.') : '—']) }}
                    </span>
                @else
                    <i class="bi bi-lock me-1"></i>
                    {{ __('The application period has closed. Contact the council to reopen your application.') }}
                @endif
            </div>
        @endif

        {{-- Admin actions: one button per allowed next status --}}
        @if ($admin && $application->nextStatuses())
            <form action="{{ route('exams.applications.status', [$application->exam, $application]) }}" method="POST" class="border rounded p-3 bg-light no-print">
                @csrf
                @method('PATCH')
                <label class="form-label" for="status_remarks">{{ __('Remarks') }}
                    <span class="text-muted small fw-normal">({{ __('required when rejecting; shown to the applicant') }})</span>
                </label>
                <textarea class="form-control mb-2 @error('remarks') is-invalid @enderror" id="status_remarks" name="remarks" rows="2" maxlength="1000">{{ old('remarks') }}</textarea>
                @error('remarks') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror
                <div class="d-flex flex-wrap gap-2">
                    @foreach ($application->nextStatuses() as $next)
                        <button type="submit" name="status" value="{{ $next }}" class="btn btn-sm {{ $buttonClass[$next] }}"
                            onclick="return confirm(@js(__('Change status to :status?', ['status' => __(ExamApplication::STATUSES[$next][0])])))">
                            {{ $actionLabel[$next] }}
                        </button>
                    @endforeach
                </div>
            </form>
        @endif

        {{-- History --}}
        @if ($application->statusLogs->isNotEmpty())
            <h6 class="application-view-subtitle">{{ __('History') }}</h6>
            <ul class="status-history">
                @foreach ($application->statusLogs as $log)
                    <li>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="badge {{ ExamApplication::STATUSES[$log->to_status][1] ?? 'bg-light text-dark' }}">{{ $log->toLabel() }}</span>
                            <span class="text-muted small">
                                {{ $log->created_at->setTimezone($tz)->format('Y-m-d H:i') }}
                                @if ($admin && $log->changedBy) · {{ $log->changedBy->email }} @endif
                            </span>
                        </div>
                        @if ($log->remarks) <div class="small mt-1">{{ $log->remarks }}</div> @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

@once
    @push('styles')
        <style>
            .status-steps { display: flex; list-style: none; padding: 0; margin: 0 0 1rem; counter-reset: step; overflow-x: auto; }
            .status-steps li { flex: 1; min-width: 96px; position: relative; text-align: center; font-size: .78rem; color: #8a94a6; }
            .status-steps li::before { content: ''; position: absolute; top: 15px; left: -50%; width: 100%; height: 2px; background: #e3e8f4; z-index: 0; }
            .status-steps li:first-child::before { display: none; }
            .status-steps li.is-done::before, .status-steps li.is-current::before, .status-steps li.is-stopped::before { background: #003893; }
            .status-step-dot {
                position: relative; z-index: 1; display: inline-flex; align-items: center; justify-content: center;
                width: 32px; height: 32px; border-radius: 50%; background: #fff; border: 2px solid #e3e8f4; font-weight: 600; margin-bottom: .35rem;
            }
            .status-step-label { display: block; padding: 0 .25rem; }
            .is-done .status-step-dot { background: #003893; border-color: #003893; color: #fff; }
            .is-current .status-step-dot { border-color: #003893; color: #003893; box-shadow: 0 0 0 4px rgba(0, 56, 147, .12); }
            .is-current, .is-done { color: #1f2937; }
            .is-stopped .status-step-dot { background: #dc3545; border-color: #dc3545; color: #fff; }
            .is-stopped { color: #dc3545; font-weight: 600; }
            .status-history { list-style: none; padding: 0 0 0 1rem; margin: 0; border-left: 2px solid #e3e8f4; }
            .status-history li { position: relative; padding: 0 0 .9rem .75rem; }
            .status-history li::before { content: ''; position: absolute; left: -1.38rem; top: .35rem; width: 10px; height: 10px; border-radius: 50%; background: #003893; }
        </style>
    @endpush
@endonce
