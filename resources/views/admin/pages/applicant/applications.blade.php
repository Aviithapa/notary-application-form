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
                                    <td><span class="badge bg-info-subtle text-info">{{ __(ucfirst($application->status)) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endsection
