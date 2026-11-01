{{-- One training. Needs $i (row key, '__INDEX__' in the template), $row (field values), $model (saved record or null). --}}
@php
    $name = fn (string $field) => "trainings[$i][$field]";
    $key = fn (string $field) => "trainings.$i.$field";
    $certificate = $model?->getFirstMedia(\App\Models\ApplicantTraining::CERTIFICATE);
@endphp

<div class="card border applicant-row" data-row>
    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
        <h6 class="m-0">{{ __('Training') }} <span class="applicant-row-number"></span></h6>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>
            <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">{{ __('Remove') }}</span>
        </button>
    </div>
    <div class="card-body">
        @if (! empty($row['id']))
            <input type="hidden" name="{{ $name('id') }}" value="{{ $row['id'] }}">
        @endif

        <div class="row g-3">
            @include('admin.pages.applicant.partials.input', ['name' => $name('training_name'), 'key' => $key('training_name'), 'id' => "t{$i}_name", 'label' => __('Training name'), 'value' => $row['training_name'] ?? null])
            @include('admin.pages.applicant.partials.input', ['name' => $name('organization'), 'key' => $key('organization'), 'id' => "t{$i}_organization", 'label' => __('Training provider'), 'value' => $row['organization'] ?? null])
            @include('admin.pages.applicant.partials.input', ['name' => $name('certificate_number'), 'key' => $key('certificate_number'), 'id' => "t{$i}_certificate_number", 'label' => __('Certificate number'), 'value' => $row['certificate_number'] ?? null, 'col' => 'col-12'])

            @include('admin.pages.applicant.partials.date-pair', [
                'label' => __('Date of issue'),
                'idPrefix' => "t{$i}_issue",
                'bsName' => $name('issue_date_bs'), 'bsKey' => $key('issue_date_bs'), 'bsValue' => $row['issue_date_bs'] ?? null,
                'adName' => $name('issue_date_ad'), 'adKey' => $key('issue_date_ad'), 'adValue' => $row['issue_date_ad'] ?? null,
            ])

            <div class="col-12">
                <label class="form-label" for="t{{ $i }}_certificate">{{ __('Certificate') }}</label>
                @if ($certificate)
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-2">
                        @include('admin.pages.applicant.partials.media-link', ['media' => $certificate])
                        <label class="form-check-label small text-danger">
                            <input type="checkbox" class="form-check-input me-1" name="{{ $name('remove_certificate') }}" value="1">
                            {{ __('Remove') }}
                        </label>
                    </div>
                @endif
                <input type="file" class="form-control @error($key('certificate')) is-invalid @enderror" id="t{{ $i }}_certificate"
                    name="{{ $name('certificate') }}" accept="image/jpeg,image/png,image/webp,application/pdf">
                @error($key('certificate')) <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">
                    {{ __('JPG, PNG, WEBP or PDF, up to 2 MB.') }}
                    @if ($certificate) {{ __('Uploading a new file replaces the current one.') }} @endif
                </div>
            </div>
        </div>
    </div>
</div>
