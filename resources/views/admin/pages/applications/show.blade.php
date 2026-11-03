@extends('admin.layout.app')

{{-- Full view of one exam application: the application itself and the applicant's form
     (personal details, qualifications, trainings). Shared by admins and the applicant. --}}
@php
    use App\Models\ApplicantProfile;
    use App\Models\ExamApplication;
    use App\Support\NepaliDate;

    $exam = $application->exam;
    $user = $application->user;
    $profile = $user->applicantProfile;
    $field = 'admin.pages.applications.partials.field';
    $money = fn ($amount) => $amount === null ? null : __('Rs.') . ' ' . number_format((float) $amount, 2);
    $date = fn ($bs, $ad) => $bs ? "$bs " . __('B.S.') . " ($ad)" : null;
    $appliedOn = $application->created_at->setTimezone(config('app.business_timezone'));
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('View application')])

    <div class="d-flex justify-content-between gap-2 mb-3 no-print">
        <a href="{{ $backUrl }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
        <div class="d-flex gap-2">
            @if ($application->hasAdmitCard())
                <a href="{{ $admin ?? false ? route('exams.applications.admit-card', [$exam, $application]) : route('applicant.applications.admit-card', $application) }}"
                    class="btn btn-success" target="_blank"><i class="bi bi-person-badge me-1"></i> {{ __('Admit card') }}</a>
            @endif
            <button type="button" class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer me-1"></i> {{ __('Print') }}</button>
        </div>
    </div>

    {{-- Application summary --}}
    <div class="card application-view-header">
        <div class="card-body">
            <div class="d-flex flex-column flex-sm-row gap-4 align-items-start">
                <div class="application-photo">
                    @if ($photo = $profile?->getFirstMedia(ApplicantProfile::PHOTO))
                        <a href="{{ route('lookup.media', $photo) }}" class="d-block w-100 h-100" data-gallery-item data-caption="{{ __('Photo') }}">
                            <img src="{{ route('lookup.media', $photo) }}" alt="{{ __('Photo') }}">
                        </a>
                    @else
                        <i class="bi bi-person-bounding-box"></i>
                    @endif
                </div>

                <div class="flex-grow-1 w-100" style="min-width: 0;">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                        <h4 class="m-0">{{ $profile?->localized('name') ?: $user->email }}</h4>
                        @include('admin.pages.applications.partials.status')
                    </div>
                    <div class="text-muted mb-3">{{ __('Application no.') }} <strong class="text-body">{{ $application->application_number }}</strong></div>

                    <div class="row g-3">
                        @include($field, ['label' => __('Exam'), 'value' => $exam->localized('title')])
                        @include($field, ['label' => __('Service'), 'value' => $exam->service->localized('name')])
                        @include($field, ['label' => __('Applied on'), 'value' => $date(NepaliDate::adToBs($appliedOn->toDateString()), $appliedOn->format('Y-m-d H:i'))])
                        @include($field, ['label' => __('Fee'), 'value' => ($money($application->fee_amount) ?? __('Free')) . ($application->is_double_fee ? ' (' . __('Double fee') . ')' : '')])
                        @include($field, ['label' => __('Amount paid'), 'value' => $money($application->paid_amount)])
                        @include($field, ['label' => __('Exam date'), 'value' => $date($exam->exam_date_bs, $exam->exam_date_ad)])
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.pages.applications.partials.status-panel', ['admin' => $admin ?? false])

    {{-- Voucher, signature, thumbprints --}}
    <div class="card">
        <div class="card-body">
            <h5 class="application-view-title"><i class="bi bi-fingerprint me-1"></i> {{ __('Payment and identity') }}</h5>
            <div class="row g-3">
                @foreach ([ExamApplication::VOUCHER => 'Bank voucher'] + ExamApplication::IDENTITY_MARKS as $collection => $label)
                    @if ($file = $application->getFirstMedia($collection))
                        <div class="col-6 col-lg-3">
                            <div class="text-muted small mb-1">{{ __($label) }}</div>
                            @php $isImage = str_starts_with((string) $file->mime_type, 'image/'); @endphp
                            <a href="{{ route('lookup.media', $file) }}" target="_blank" rel="noopener" class="application-mark"
                                @if ($isImage) data-gallery-item data-caption="{{ __($label) }}" @endif>
                                @if ($isImage)
                                    <img src="{{ route('lookup.media', $file) }}" alt="{{ __($label) }}">
                                @else
                                    <span class="text-center small"><i class="bi bi-file-earmark-pdf text-danger fs-2 d-block"></i>{{ $file->file_name }}</span>
                                @endif
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    @include('admin.pages.applications.partials.profile', ['user' => $user])
@endsection

@push('styles')
    <style>
        .application-view-header { border-top: 3px solid #003893; }
        .application-photo {
            width: 120px; height: 150px; flex: none; border: 1px solid #dee2e6; border-radius: 8px; background: #f8f9fc;
            display: flex; align-items: center; justify-content: center; overflow: hidden; color: #98a6c2; font-size: 3rem;
        }
        .application-photo img { width: 100%; height: 100%; object-fit: cover; }
        .application-mark {
            height: 130px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff;
            display: flex; align-items: center; justify-content: center; overflow: hidden; text-decoration: none;
        }
        .application-mark img { max-width: 100%; max-height: 100%; object-fit: contain; }

        @media print {
            .leftside-menu, .navbar-custom, .no-print, .page-title-right { display: none !important; }
            .content-page { margin-left: 0 !important; padding: 0 !important; }
            .card { break-inside: avoid; box-shadow: none !important; border: 1px solid #dee2e6; }
        }
    </style>
@endpush
