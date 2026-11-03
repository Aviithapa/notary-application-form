{{--
    The applicant's form: personal details, qualifications and trainings with their documents.
    Needs $user with applicantProfile (and its places/media), qualifications.media and trainings.media
    loaded (see User::loadApplicationForm()). Images carry data-gallery-item for the image viewer.
--}}
@php
    use App\Models\ApplicantProfile;

    $profile = $user->applicantProfile;
    $nepali = app()->getLocale() === 'np';
    $field = 'admin.pages.applications.partials.field';
    $place = fn ($model) => $model ? ($nepali ? $model->name_np : $model->name_en) : null;
    $address = fn ($tole, $ward, $municipality, $district) => collect([
        $tole,
        $ward ? __('Ward no.') . ' ' . $ward->ward_number : null,
        $place($municipality),
        $place($district),
    ])->filter()->implode(', ');
    $date = fn ($bs, $ad) => $bs ? "$bs " . __('B.S.') . " ($ad)" : null;
@endphp

{{-- Personal details --}}
<div class="card">
    <div class="card-body">
        <h5 class="application-view-title"><i class="bi bi-person-vcard me-1"></i> {{ __('Personal details') }}</h5>

        @if (! $profile)
            <p class="text-muted mb-0">{{ __('The applicant has not filled in personal details.') }}</p>
        @else
            <div class="row g-3">
                @include($field, ['label' => __('Full name (English)'), 'value' => $profile->name])
                @include($field, ['label' => __('Full name (Nepali)'), 'value' => $profile->name_np])
                @include($field, ['label' => __('Gender'), 'value' => $profile->gender ? __(ucfirst($profile->gender)) : null])
                @include($field, ['label' => __('Date of birth'), 'value' => $date($profile->dob_bs, $profile->dob_ad)])
                @include($field, ['label' => __('Occupation'), 'value' => $profile->occupation])
                @include($field, ['label' => __('Religion'), 'value' => $profile->religion])
                @include($field, ['label' => __('Mother tongue'), 'value' => $profile->mother_tongue])
                @include($field, ['label' => __('Caste'), 'value' => $profile->caste])
                @include($field, ['label' => __('Physical description (हुलिया)'), 'value' => $profile->huliya])
            </div>

            <h6 class="application-view-subtitle">{{ __('Citizenship') }}</h6>
            <div class="row g-3">
                @include($field, ['label' => __('Citizenship number'), 'value' => $profile->citizenship_number])
                @include($field, ['label' => __('Citizenship issued district'), 'value' => $place($profile->citizenshipIssueDistrict)])
                <div class="col-lg-4">
                    <div class="text-muted small mb-1">{{ __('Citizenship (front / back)') }}</div>
                    <div class="d-flex gap-3">
                        @foreach ([ApplicantProfile::CITIZENSHIP_FRONT => __('Citizenship (front)'), ApplicantProfile::CITIZENSHIP_BACK => __('Citizenship (back)')] as $collection => $label)
                            @if ($file = $profile->getFirstMedia($collection))
                                @include('admin.pages.applicant.partials.media-link', ['media' => $file, 'caption' => $label])
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <h6 class="application-view-subtitle">{{ __('Family details') }}</h6>
            <div class="row g-3">
                @foreach (['mother_name' => __("Mother's name"), 'father_name' => __("Father's name"), 'grandfather_name' => __("Grandfather's name")] as $key => $label)
                    @include($field, ['label' => $label, 'value' => collect([$profile->{$key}, $profile->{$key . '_np'}])->filter()->implode(' / ')])
                @endforeach
            </div>

            <h6 class="application-view-subtitle">{{ __('Address and contact') }}</h6>
            <div class="row g-3">
                @include($field, ['label' => __('Permanent address'), 'value' => $address($profile->tole, $profile->ward, $profile->municipality, $profile->district), 'col' => 'col-lg-6'])
                @include($field, ['label' => __('Correspondence or office address'), 'value' => $address($profile->correspondence_tole, $profile->correspondenceWard, $profile->correspondenceMunicipality, $profile->correspondenceDistrict), 'col' => 'col-lg-6'])
                @include($field, ['label' => __('Phone'), 'value' => $profile->phone])
                @include($field, ['label' => __('Fax'), 'value' => $profile->fax])
                @include($field, ['label' => __('Email'), 'value' => $profile->email ?: $user->email])
            </div>
        @endif
    </div>
</div>

{{-- Qualifications --}}
<div class="card">
    <div class="card-body">
        <h5 class="application-view-title"><i class="bi bi-mortarboard me-1"></i> {{ __('Qualifications') }}</h5>
        @if ($user->qualifications->isEmpty())
            <p class="text-muted mb-0">{{ __('No qualifications added.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Level passed') }}</th>
                            <th>{{ __('University / Board') }}</th>
                            <th>{{ __('Passed year') }}</th>
                            <th>{{ __('Division / Grade') }}</th>
                            <th>{{ __('Percentage / GPA') }}</th>
                            <th>{{ __('Documents') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->qualifications as $qualification)
                            <tr>
                                <td class="fw-semibold">{{ $qualification->level ?: '—' }}</td>
                                <td>{{ $qualification->university_name ?: '—' }}</td>
                                <td>{{ $qualification->passed_year ?: '—' }}</td>
                                <td>{{ $qualification->division ? __($qualification->division) : '—' }}</td>
                                <td>{{ $qualification->percentage ?: '—' }}</td>
                                <td>
                                    <div class="d-flex flex-wrap gap-2">
                                        @forelse ($qualification->getMedia(\App\Models\ApplicantQualification::DOCUMENTS) as $document)
                                            @include('admin.pages.applicant.partials.media-link', ['media' => $document, 'caption' => ($qualification->level ?: __('Qualification')) . ' · ' . $document->file_name])
                                        @empty
                                            <span class="text-muted">—</span>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Trainings --}}
<div class="card">
    <div class="card-body">
        <h5 class="application-view-title"><i class="bi bi-award me-1"></i> {{ __('Trainings') }}</h5>
        @if ($user->trainings->isEmpty())
            <p class="text-muted mb-0">{{ __('No trainings added.') }}</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('Training name') }}</th>
                            <th>{{ __('Training provider') }}</th>
                            <th>{{ __('Certificate number') }}</th>
                            <th>{{ __('Date of issue') }}</th>
                            <th>{{ __('Certificate') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($user->trainings as $training)
                            <tr>
                                <td class="fw-semibold">{{ $training->training_name ?: '—' }}</td>
                                <td>{{ $training->organization ?: '—' }}</td>
                                <td>{{ $training->certificate_number ?: '—' }}</td>
                                <td class="text-nowrap">{{ $date($training->issue_date_bs, $training->issue_date_ad) ?? '—' }}</td>
                                <td>
                                    @if ($certificate = $training->getFirstMedia(\App\Models\ApplicantTraining::CERTIFICATE))
                                        @include('admin.pages.applicant.partials.media-link', ['media' => $certificate, 'caption' => __('Certificate') . ' · ' . ($training->training_name ?: $certificate->file_name)])
                                    @else
                                        <span class="text-muted">—</span>
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

@once
    @push('styles')
        <style>
            .application-view-title { color: #003893; font-weight: 600; border-bottom: 1px solid #eef1f7; padding-bottom: .5rem; margin-bottom: 1rem; }
            .application-view-subtitle { color: #6c757d; text-transform: uppercase; font-size: .75rem; letter-spacing: .04em; margin: 1.5rem 0 .75rem; }
            .applicant-thumb { height: 56px; width: auto; max-width: 100px; object-fit: cover; }
        </style>
    @endpush
@endonce

@include('admin.pages.applications.partials.image-viewer')
