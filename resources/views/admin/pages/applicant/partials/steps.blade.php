{{-- Step header for the applicant form. Needs $step (current key) and $progress (key => has data). --}}
@php
    $steps = [
        'personal' => ['label' => __('Personal details'), 'icon' => 'bi-person-vcard'],
        'qualifications' => ['label' => __('Qualifications'), 'icon' => 'bi-mortarboard'],
        'trainings' => ['label' => __('Trainings'), 'icon' => 'bi-award'],
    ];
@endphp

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">{{ __('Application form') }}</h4>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <ul class="nav nav-pills nav-justified flex-column flex-md-row gap-1 applicant-steps">
            @foreach ($steps as $key => $item)
                <li class="nav-item">
                    <a href="{{ route('applicant.' . $key) }}"
                        class="nav-link d-flex align-items-center justify-content-center gap-2 {{ $step === $key ? 'active' : '' }}"
                        @if ($step === $key) aria-current="step" @endif>
                        <span class="applicant-step-number">{{ $loop->iteration }}</span>
                        <i class="bi {{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if ($progress[$key])
                            <i class="bi bi-check-circle-fill {{ $step === $key ? 'text-white' : 'text-success' }}"
                                title="{{ __('Saved') }}"></i>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@if ($locked ?? false)
    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-lock-fill mt-1"></i>
        <div>
            <strong>{{ __('Your details are locked.') }}</strong>
            {{ __('An application has been approved, so these details can no longer be changed. Contact the council if something needs correcting.') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle me-1"></i>
        {{ __('Please correct the highlighted fields below. Files you selected need to be chosen again.') }}
    </div>
@endif

@once
    @push('styles')
        <style>
            .applicant-steps .nav-link { color: #003893; border: 1px solid #e3e8f4; }
            .applicant-steps .nav-link.active { background-color: #003893; border-color: #003893; color: #fff; }
            .applicant-step-number {
                display: inline-flex; align-items: center; justify-content: center;
                width: 24px; height: 24px; border-radius: 50%;
                background: rgba(0, 56, 147, .1); font-size: .8rem; font-weight: 600;
            }
            .applicant-steps .nav-link.active .applicant-step-number { background: rgba(255, 255, 255, .2); }
            .applicant-section-title { color: #003893; font-weight: 600; border-bottom: 1px solid #eef1f7; padding-bottom: .5rem; margin-bottom: 1rem; }
            .applicant-rows { counter-reset: applicant-row; }
            .applicant-row { counter-increment: applicant-row; }
            .applicant-row-number::after { content: counter(applicant-row); }
            .applicant-thumb { height: 64px; width: auto; max-width: 120px; object-fit: cover; }
            .applicant-fieldset { min-width: 0; }
            .applicant-fieldset:disabled [data-add-row], .applicant-fieldset:disabled [data-remove-row] { display: none; }
            .applicant-actions { position: sticky; bottom: 0; z-index: 5; background: var(--ct-body-bg, #f5f7fb); padding: .75rem 0; }
        </style>
    @endpush
@endonce
