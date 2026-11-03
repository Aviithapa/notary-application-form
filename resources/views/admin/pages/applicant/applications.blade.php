@extends('admin.layout.app')

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('My applications')])

    @if ($applications->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size: 2.25rem;"></i>
                <p class="text-muted mt-2">{{ __('You have not applied for any exam yet.') }}</p>
                <a href="{{ route('applicant.exams') }}" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                    {{ __('See open exams') }}
                </a>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Application no.') }}</th>
                                <th>{{ __('Exam') }}</th>
                                <th>{{ __('Applied on') }}</th>
                                <th>{{ __('Fee') }}</th>
                                <th>{{ __('Exam date') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th class="text-end">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($applications as $application)
                                @php $appliedOn = $application->created_at->setTimezone(config('app.business_timezone'))->toDateString(); @endphp
                                <tr>
                                    <td class="fw-semibold text-nowrap">{{ $application->application_number }}</td>
                                    <td>
                                        <div>{{ $application->exam->localized('title') }}</div>
                                        <div class="text-muted small">{{ $application->exam->service->localized('name') }}</div>
                                    </td>
                                    <td class="text-nowrap small">
                                        {{ \App\Support\NepaliDate::adToBs($appliedOn) }} {{ __('B.S.') }}
                                        <div class="text-muted">{{ $appliedOn }}</div>
                                    </td>
                                    <td class="text-nowrap">
                                        @if ($application->fee_amount !== null)
                                            {{ __('Rs.') }} {{ number_format((float) $application->fee_amount, 2) }}
                                        @else
                                            {{ __('Free') }}
                                        @endif
                                        @if ($application->is_double_fee)
                                            <div><span class="badge bg-warning-subtle text-warning">{{ __('Double fee') }}</span></div>
                                        @endif
                                        @if ($application->paid_amount !== null)
                                            <div class="text-muted small">{{ __('Paid') }}: {{ __('Rs.') }} {{ number_format((float) $application->paid_amount, 2) }}</div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap small">
                                        @if ($application->exam->exam_date_bs)
                                            {{ $application->exam->exam_date_bs }} {{ __('B.S.') }}
                                            @if ($application->exam->start_time)
                                                <div class="text-muted">{{ substr($application->exam->start_time, 0, 5) }}</div>
                                            @endif
                                        @else
                                            <span class="text-muted">{{ __('To be announced') }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @include('admin.pages.applications.partials.status')
                                        @if ($application->roll_number)
                                            <div class="small mt-1">{{ __('Roll no.') }} <strong>{{ $application->rollNumberLabel() }}</strong></div>
                                        @endif
                                        @if ($application->status === \App\Models\ExamApplication::REJECTED && $application->remarks)
                                            <div class="small text-danger mt-1" style="max-width: 240px;">{{ $application->remarks }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('applicant.applications.show', $application) }}" class="btn btn-sm btn-primary text-nowrap" style="background-color: #003893; border-color: #003893;">
                                            <i class="bi bi-eye"></i> {{ __('View application') }}
                                        </a>
                                        @if ($application->canResubmit())
                                            <a href="{{ route('applicant.applications.resubmit.form', $application) }}" class="btn btn-sm btn-warning text-nowrap mt-1">
                                                <i class="bi bi-arrow-repeat"></i> {{ __('Edit & resubmit') }}
                                            </a>
                                        @endif
                                        @if ($application->hasAdmitCard())
                                            <a href="{{ route('applicant.applications.admit-card', $application) }}" class="btn btn-sm btn-success text-nowrap mt-1" target="_blank">
                                                <i class="bi bi-person-badge"></i> {{ __('Admit card') }}
                                            </a>
                                        @endif
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
