@extends('admin.layout.app')

@php
    $v = fn (string $field) => old($field, $profile->{$field});
    $nepali = app()->getLocale() === 'np';
@endphp

@section('content')
    @include('admin.pages.applicant.partials.steps')

    <form action="{{ route('applicant.personal.save') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf

        {{-- Basic information --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-person me-1"></i> {{ __('Basic information') }}</h5>
                <div class="row g-3">
                    <div class="col-lg-9">
                        <div class="row g-3">
                            @include('admin.pages.applicant.partials.input', ['name' => 'name', 'label' => __('Full name (English)'), 'value' => $v('name')])
                            @include('admin.pages.applicant.partials.input', ['name' => 'name_np', 'label' => __('Full name (Nepali)'), 'value' => $v('name_np'), 'lang' => 'ne'])

                            <div class="col-md-6">
                                <label class="form-label" for="gender">{{ __('Gender') }}</label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach ($genders as $gender)
                                        <option value="{{ $gender }}" @selected($v('gender') === $gender)>{{ __(ucfirst($gender)) }}</option>
                                    @endforeach
                                </select>
                                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @include('admin.pages.applicant.partials.input', ['name' => 'occupation', 'label' => __('Occupation'), 'value' => $v('occupation')])

                            @include('admin.pages.applicant.partials.date-pair', [
                                'label' => __('Date of birth'),
                                'idPrefix' => 'dob',
                                'bsName' => 'dob_bs', 'bsKey' => 'dob_bs', 'bsValue' => $v('dob_bs'),
                                'adName' => 'dob_ad', 'adKey' => 'dob_ad', 'adValue' => $v('dob_ad'),
                                'maxToday' => true,
                            ])
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <label class="form-label" for="photo">{{ __('Photo') }}</label>
                        <div class="border rounded d-flex align-items-center justify-content-center bg-light mb-2" style="height: 170px;">
                            @if ($photo = $profile->exists ? $profile->getFirstMedia(\App\Models\ApplicantProfile::PHOTO) : null)
                                <a href="{{ route('lookup.media', $photo) }}" target="_blank" rel="noopener" class="h-100 p-1">
                                    <img src="{{ route('lookup.media', $photo) }}" alt="{{ __('Photo') }}" class="h-100 w-auto rounded" style="object-fit: cover;">
                                </a>
                            @else
                                <i class="bi bi-person-bounding-box text-muted" style="font-size: 3rem;"></i>
                            @endif
                        </div>
                        <input type="file" class="form-control @error('photo') is-invalid @enderror" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">{{ __('Passport-size photo. JPG, PNG or WEBP, up to 2 MB.') }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Permanent address --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-house me-1"></i> {{ __('Permanent address') }}</h5>
                @include('admin.pages.applicant.partials.address', [
                    'prefix' => '',
                    'municipalities' => $municipalities,
                    'wards' => $wards,
                    'values' => ['district_id' => $v('district_id'), 'municipality_id' => $v('municipality_id'), 'ward_id' => $v('ward_id'), 'tole' => $v('tole')],
                ])
            </div>
        </div>

        {{-- Citizenship --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-credit-card-2-front me-1"></i> {{ __('Citizenship') }}</h5>
                <div class="row g-3">
                    @include('admin.pages.applicant.partials.input', ['name' => 'citizenship_number', 'label' => __('Citizenship number'), 'value' => $v('citizenship_number')])

                    <div class="col-md-6">
                        <label class="form-label" for="citizenship_issue_district_id">{{ __('Citizenship issued district') }}</label>
                        <select class="form-select @error('citizenship_issue_district_id') is-invalid @enderror" id="citizenship_issue_district_id" name="citizenship_issue_district_id">
                            <option value="">{{ __('Select district') }}</option>
                            @foreach ($districts as $district)
                                <option value="{{ $district->id }}" @selected((string) $district->id === (string) $v('citizenship_issue_district_id'))>
                                    {{ $nepali ? $district->name_np : $district->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('citizenship_issue_district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @foreach ([\App\Models\ApplicantProfile::CITIZENSHIP_FRONT => __('Citizenship (front)'), \App\Models\ApplicantProfile::CITIZENSHIP_BACK => __('Citizenship (back)')] as $collection => $label)
                        <div class="col-md-6">
                            <label class="form-label" for="{{ $collection }}">{{ $label }}</label>
                            @if ($file = $profile->exists ? $profile->getFirstMedia($collection) : null)
                                <div class="mb-2">@include('admin.pages.applicant.partials.media-link', ['media' => $file])</div>
                            @endif
                            <input type="file" class="form-control @error($collection) is-invalid @enderror" id="{{ $collection }}" name="{{ $collection }}" accept="image/jpeg,image/png,image/webp,application/pdf">
                            @error($collection) <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">
                                {{ __('JPG, PNG, WEBP or PDF, up to 2 MB.') }}
                                @if ($file) {{ __('Uploading a new file replaces the current one.') }} @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Other details --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-card-list me-1"></i> {{ __('Other details') }}</h5>
                <div class="row g-3">
                    @include('admin.pages.applicant.partials.input', ['name' => 'religion', 'label' => __('Religion'), 'value' => $v('religion'), 'col' => 'col-md-4', 'list' => 'religion-options'])
                    @include('admin.pages.applicant.partials.input', ['name' => 'mother_tongue', 'label' => __('Mother tongue'), 'value' => $v('mother_tongue'), 'col' => 'col-md-4', 'list' => 'mother-tongue-options'])
                    @include('admin.pages.applicant.partials.input', ['name' => 'caste', 'label' => __('Caste'), 'value' => $v('caste'), 'col' => 'col-md-4'])

                    <div class="col-12">
                        <label class="form-label" for="huliya">{{ __('Physical description (हुलिया)') }}</label>
                        <textarea class="form-control @error('huliya') is-invalid @enderror" id="huliya" name="huliya" rows="2" maxlength="1000"
                            placeholder="{{ __('e.g. mole on the left cheek') }}">{{ $v('huliya') }}</textarea>
                        @error('huliya') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <datalist id="religion-options">
                    @foreach (['Hindu', 'Buddhist', 'Islam', 'Kirat', 'Christian', 'Jain', 'Sikh'] as $option)
                        <option value="{{ __($option) }}"></option>
                    @endforeach
                </datalist>
                <datalist id="mother-tongue-options">
                    @foreach (['Nepali', 'Maithili', 'Bhojpuri', 'Tharu', 'Tamang', 'Nepal Bhasa', 'Magar', 'Bajjika', 'Doteli', 'Urdu', 'Avadhi', 'Limbu', 'Gurung', 'Rai'] as $option)
                        <option value="{{ __($option) }}"></option>
                    @endforeach
                </datalist>
            </div>
        </div>

        {{-- Family --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-people me-1"></i> {{ __('Family details') }}</h5>
                <div class="row g-3">
                    @foreach (['mother_name' => __("Mother's name"), 'father_name' => __("Father's name"), 'grandfather_name' => __("Grandfather's name")] as $field => $label)
                        @include('admin.pages.applicant.partials.input', ['name' => $field, 'label' => $label . ' (' . __('English') . ')', 'value' => $v($field)])
                        @include('admin.pages.applicant.partials.input', ['name' => $field . '_np', 'label' => $label . ' (' . __('Nepali') . ')', 'value' => $v($field . '_np'), 'lang' => 'ne'])
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Correspondence / office address --}}
        <div class="card">
            <div class="card-body">
                <h5 class="applicant-section-title"><i class="bi bi-envelope-paper me-1"></i> {{ __('Correspondence or office address') }}</h5>
                @include('admin.pages.applicant.partials.address', [
                    'prefix' => 'correspondence_',
                    'municipalities' => $corrMunicipalities,
                    'wards' => $corrWards,
                    'values' => [
                        'district_id' => $v('correspondence_district_id'),
                        'municipality_id' => $v('correspondence_municipality_id'),
                        'ward_id' => $v('correspondence_ward_id'),
                        'tole' => $v('correspondence_tole'),
                    ],
                ])
                <div class="row g-3 mt-0">
                    @include('admin.pages.applicant.partials.input', ['name' => 'phone', 'label' => __('Phone'), 'value' => $v('phone'), 'type' => 'tel', 'col' => 'col-md-4'])
                    @include('admin.pages.applicant.partials.input', ['name' => 'fax', 'label' => __('Fax'), 'value' => $v('fax'), 'type' => 'tel', 'col' => 'col-md-4'])
                    @include('admin.pages.applicant.partials.input', ['name' => 'email', 'label' => __('Email'), 'value' => $v('email'), 'type' => 'email', 'col' => 'col-md-4'])
                </div>
            </div>
        </div>

        <div class="applicant-actions d-flex justify-content-end gap-2">
            <button type="submit" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                {{ __('Save and continue') }} <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </div>
    </form>
@endsection

@include('admin.pages.applicant.partials.scripts')
