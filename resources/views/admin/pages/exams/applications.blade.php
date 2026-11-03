@extends('admin.layout.app')

@push('styles')
    <style>
        .application-docs .applicant-thumb { height: 36px; width: 36px; object-fit: cover; }
        .application-status-tabs .nav-link { padding: .3rem .7rem; font-size: .85rem; border: 1px solid #e3e8f4; }
        .application-status-tabs .nav-link.active { background-color: #003893; border-color: #003893; }
    </style>
@endpush

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

    {{-- Filter by status --}}
    <ul class="nav nav-pills flex-wrap gap-1 mb-3 application-status-tabs">
        <li class="nav-item">
            <a href="{{ route('exams.applications', $exam) }}" class="nav-link {{ $status ? '' : 'active' }}">
                {{ __('All') }} <span class="badge bg-light text-dark ms-1">{{ $statusCounts->sum() }}</span>
            </a>
        </li>
        @foreach (\App\Models\ExamApplication::STATUSES as $key => [$label])
            <li class="nav-item">
                <a href="{{ route('exams.applications', [$exam, 'status' => $key]) }}" class="nav-link {{ $status === $key ? 'active' : '' }}">
                    {{ __($label) }} <span class="badge bg-light text-dark ms-1">{{ $statusCounts[$key] ?? 0 }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Bulk status change for the ticked rows (the checkboxes belong to this form via form="bulk-status") --}}
    <form id="bulk-status" action="{{ route('exams.applications.bulk-status', $exam) }}" method="POST"
        class="card mb-3" onsubmit="return confirm(@js(__('Change the status of the selected applications?')))">
        @csrf
        @method('PATCH')
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
            <span class="small text-muted"><span data-selected-count>0</span> {{ __('selected') }}</span>
            <select name="status" class="form-select form-select-sm" style="width: auto;" required aria-label="{{ __('New status') }}">
                <option value="">{{ __('Change status to…') }}</option>
                @foreach (\App\Models\ExamApplication::STATUSES as $key => [$label])
                    @continue($key === \App\Models\ExamApplication::PENDING)
                    <option value="{{ $key }}">{{ __($label) }}</option>
                @endforeach
            </select>
            <input type="text" name="remarks" class="form-control form-control-sm" style="max-width: 320px;" maxlength="1000"
                placeholder="{{ __('Remarks (required when rejecting)') }}" aria-label="{{ __('Remarks') }}">
            <button type="submit" class="btn btn-sm btn-primary" data-bulk-submit disabled>{{ __('Apply') }}</button>
            <span class="small text-muted ms-auto d-none d-lg-inline">{{ __('Applications that cannot move to the chosen status are skipped.') }}</span>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-0">
            @if ($applications->isEmpty())
                <div class="text-center text-muted py-5">{{ __('No applications yet.') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 1%;">
                                    <input type="checkbox" class="form-check-input" data-select-all aria-label="{{ __('Select all') }}">
                                </th>
                                <th>{{ __('Application no.') }}</th>
                                <th>{{ __('Applicant') }}</th>
                                <th>{{ __('Applied on') }}</th>
                                <th>{{ __('Fee') }} / {{ __('Paid') }}</th>
                                <th>{{ __('Documents') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                @php $profile = $application->user->applicantProfile; @endphp
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input" name="applications[]" value="{{ $application->id }}"
                                            form="bulk-status" data-select-row aria-label="{{ $application->application_number }}">
                                    </td>
                                    <td class="fw-semibold text-nowrap">
                                        {{ $application->application_number }}
                                        @if ($application->roll_number)
                                            <div class="text-muted small fw-normal">{{ __('Roll no.') }} {{ $application->rollNumberLabel() }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $profile?->localized('name') ?: '—' }}</div>
                                        <div class="text-muted small">{{ $application->user->email }}</div>
                                    </td>
                                    <td class="text-nowrap small">
                                        {{ \App\Support\NepaliDate::adToBs($application->created_at->setTimezone(config('app.business_timezone'))->toDateString()) }} {{ __('B.S.') }}
                                        <div class="text-muted">{{ $application->created_at->setTimezone(config('app.business_timezone'))->format('Y-m-d H:i') }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($application->fee_amount !== null)
                                            {{ __('Rs.') }} {{ number_format((float) $application->fee_amount, 2) }}
                                        @else
                                            {{ __('Free') }}
                                        @endif
                                        @if ($application->is_double_fee)
                                            <span class="badge bg-warning-subtle text-warning">{{ __('Double fee') }}</span>
                                        @endif
                                        @if ($application->paid_amount !== null)
                                            <div class="small {{ (float) $application->paid_amount < (float) $application->fee_amount ? 'text-danger' : 'text-muted' }}">
                                                {{ __('Paid') }}: {{ number_format((float) $application->paid_amount, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 application-docs">
                                            @foreach ([\App\Models\ExamApplication::VOUCHER => __('Bank voucher')] + \App\Models\ExamApplication::IDENTITY_MARKS as $collection => $label)
                                                @if ($file = $application->getFirstMedia($collection))
                                                    @include('admin.pages.applicant.partials.media-link', ['media' => $file, 'caption' => __($label)])
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>@include('admin.pages.applications.partials.status')</td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('exams.applications.show', [$exam, $application]) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> {{ __('View') }}
                                        </a>
                                        @if ($application->hasAdmitCard())
                                            <a href="{{ route('exams.applications.admit-card', [$exam, $application]) }}" class="btn btn-sm btn-light" target="_blank" title="{{ __('Admit card') }}">
                                                <i class="bi bi-person-badge"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-2">{{ $applications->links('pagination::bootstrap-5') }}</div>

    @push('scripts')
        <script>
            (function () {
                const all = document.querySelector('[data-select-all]');
                const rows = () => [...document.querySelectorAll('[data-select-row]')];
                const count = document.querySelector('[data-selected-count]');
                const submit = document.querySelector('[data-bulk-submit]');
                const refresh = () => {
                    const selected = rows().filter((row) => row.checked).length;
                    count.textContent = selected;
                    submit.disabled = selected === 0;
                    if (all) {
                        all.checked = selected > 0 && selected === rows().length;
                        all.indeterminate = selected > 0 && selected < rows().length;
                    }
                };
                all?.addEventListener('change', () => { rows().forEach((row) => { row.checked = all.checked; }); refresh(); });
                document.addEventListener('change', (event) => { if (event.target.matches('[data-select-row]')) refresh(); });
                refresh();
            })();
        </script>
    @endpush

    <a href="{{ route('exams.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
@endsection
