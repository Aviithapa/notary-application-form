@extends('admin.layout.app')

@push('styles')
    <style>.applicant-thumb { height: 48px; width: auto; max-width: 80px; object-fit: cover; }</style>
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

    <div class="card">
        <div class="card-body p-0">
            @if ($applications->isEmpty())
                <div class="text-center text-muted py-5">{{ __('No applications yet.') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Application no.') }}</th>
                                <th>{{ __('Applicant') }}</th>
                                <th>{{ __('Applied on') }}</th>
                                <th>{{ __('Fee') }}</th>
                                <th>{{ __('Paid') }}</th>
                                <th>{{ __('Documents') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                @php $profile = $application->user->applicantProfile; @endphp
                                <tr>
                                    <td class="fw-semibold text-nowrap">{{ $application->application_number }}</td>
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
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($application->paid_amount !== null)
                                            {{ __('Rs.') }} {{ number_format((float) $application->paid_amount, 2) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach ([\App\Models\ExamApplication::VOUCHER => __('Bank voucher')] + \App\Models\ExamApplication::IDENTITY_MARKS as $collection => $label)
                                                @if ($file = $application->getFirstMedia($collection))
                                                    <div class="text-center" title="{{ __($label) }}">
                                                        @include('admin.pages.applicant.partials.media-link', ['media' => $file])
                                                        <div class="text-muted" style="font-size: .7rem;">{{ __($label) }}</div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info-subtle text-info">{{ __(ucfirst($application->status)) }}</span></td>
                                    <td class="text-end">
                                        <a href="{{ route('exams.applications.show', [$exam, $application]) }}" class="btn btn-sm btn-primary text-nowrap">
                                            <i class="bi bi-eye"></i> {{ __('View') }}
                                        </a>
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

    <a href="{{ route('exams.index') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
@endsection
