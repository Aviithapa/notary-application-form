@extends('admin.layout.app')

@php
    $editing = $service->exists;
    $v = fn (string $field) => old($field, $service->{$field});
    $field = 'admin.pages.applicant.partials.input';
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => $editing ? __('Edit service') : __('Add service')])

    <form action="{{ $editing ? route('services.update', $service) : route('services.store') }}" method="POST" novalidate>
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">{{ __('Service details') }}</h5>
                        <div class="row g-3">
                            @include($field, ['name' => 'name', 'label' => __('Service name (English)') . ' *', 'value' => $v('name')])
                            @include($field, ['name' => 'name_np', 'label' => __('Service name (Nepali)') . ' *', 'value' => $v('name_np'), 'lang' => 'ne'])

                            <div class="col-md-6">
                                <label class="form-label" for="description">{{ __('Description (English)') }}</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="2000">{{ $v('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="description_np">{{ __('Description (Nepali)') }}</label>
                                <textarea class="form-control @error('description_np') is-invalid @enderror" id="description_np" name="description_np" rows="3" maxlength="2000" lang="ne">{{ $v('description_np') }}</textarea>
                                @error('description_np') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="purpose">{{ __('Purpose') }} *</label>
                                <select class="form-select @error('purpose') is-invalid @enderror" id="purpose" name="purpose">
                                    <option value="">{{ __('Select') }}</option>
                                    @foreach (\App\Models\Service::PURPOSES as $key => $label)
                                        <option value="{{ $key }}" @selected($v('purpose') === $key)>{{ __($label) }}</option>
                                    @endforeach
                                </select>
                                @error('purpose') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            @include($field, ['name' => 'sort_order', 'label' => __('Display order'), 'value' => $v('sort_order'), 'type' => 'number', 'hint' => __('Lower numbers are shown first.')])

                            <div class="col-12">
                                <label class="form-label d-block">{{ __('Card icon') }}</label>
                                <div class="d-flex flex-wrap gap-2" role="radiogroup">
                                    @foreach (\App\Models\Service::ICONS as $icon)
                                        <input type="radio" class="btn-check" name="icon" id="icon-{{ $icon }}" value="{{ $icon }}" @checked($v('icon') === $icon)>
                                        <label class="btn btn-outline-primary service-icon-option" for="icon-{{ $icon }}" title="{{ $icon }}">
                                            <i class="bi {{ $icon }}"></i>
                                        </label>
                                    @endforeach
                                </div>
                                @error('icon') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">{{ __('Options') }}</h5>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_fee_based" name="is_fee_based" value="1"
                                @checked($v('is_fee_based')) data-toggles="fee-amount">
                            <label class="form-check-label" for="is_fee_based">{{ __('Fee based') }}</label>
                        </div>
                        <div class="mb-3 ps-4" id="fee-amount" @unless ($v('is_fee_based')) hidden @endunless>
                            <label class="form-label" for="fee_amount">{{ __('Fee amount') }} *</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text">{{ __('Rs.') }}</span>
                                <input type="number" step="0.01" min="0" class="form-control @error('fee_amount') is-invalid @enderror"
                                    id="fee_amount" name="fee_amount" value="{{ $v('fee_amount') }}">
                                @error('fee_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="requires_exam" name="requires_exam" value="1" @checked($v('requires_exam'))>
                            <label class="form-check-label" for="requires_exam">{{ __('Exam associated') }}</label>
                        </div>
                        <p class="form-text mt-0 mb-3 ps-4">{{ __('Applicants must pass an exam. You can create exams for this service.') }}</p>

                        <div class="form-check form-switch mb-1">
                            <input class="form-check-input" type="checkbox" role="switch" id="requires_application_form" name="requires_application_form" value="1" @checked($v('requires_application_form'))>
                            <label class="form-check-label" for="requires_application_form">{{ __('Application form required') }}</label>
                        </div>
                        <p class="form-text mt-0 mb-3 ps-4">{{ __('Applicants must complete their personal details, qualifications and trainings.') }}</p>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked($v('is_active'))>
                            <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                        </div>
                        <p class="form-text mt-0 ps-4">{{ __('Inactive services are hidden from applicants.') }}</p>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('services.index') }}" class="btn btn-light">{{ __('Cancel') }}</a>
                    <button type="submit" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                        <i class="bi bi-check2 me-1"></i> {{ $editing ? __('Save changes') : __('Create service') }}
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .service-icon-option { width: 48px; height: 48px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.35rem; }
    </style>
@endpush

@push('scripts')
    <script>
        // Show the fee amount only while "Fee based" is on
        document.querySelectorAll('[data-toggles]').forEach((toggle) => {
            const target = document.getElementById(toggle.dataset.toggles);
            toggle.addEventListener('change', () => { target.hidden = !toggle.checked; });
        });
    </script>
@endpush
