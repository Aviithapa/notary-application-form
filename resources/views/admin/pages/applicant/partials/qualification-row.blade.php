{{-- One qualification. Needs $i (row key, '__INDEX__' in the template), $row (field values), $model (saved record or null). --}}
@php
    $name = fn (string $field) => "qualifications[$i][$field]";
    $key = fn (string $field) => "qualifications.$i.$field";
    $documents = $model ? $model->getMedia(\App\Models\ApplicantQualification::DOCUMENTS) : collect();
@endphp

<div class="card border applicant-row" data-row>
    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2">
        <h6 class="m-0">{{ __('Qualification') }} <span class="applicant-row-number"></span></h6>
        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-row>
            <i class="bi bi-trash"></i> <span class="d-none d-sm-inline">{{ __('Remove') }}</span>
        </button>
    </div>
    <div class="card-body">
        @if (! empty($row['id']))
            <input type="hidden" name="{{ $name('id') }}" value="{{ $row['id'] }}">
        @endif

        <div class="row g-3">
            @include('admin.pages.applicant.partials.input', ['name' => $name('university_name'), 'key' => $key('university_name'), 'id' => "q{$i}_university", 'label' => __('University / Board'), 'value' => $row['university_name'] ?? null])
            @include('admin.pages.applicant.partials.input', ['name' => $name('level'), 'key' => $key('level'), 'id' => "q{$i}_level", 'label' => __('Level passed'), 'value' => $row['level'] ?? null, 'list' => 'qualification-levels', 'col' => 'col-md-6'])
            @include('admin.pages.applicant.partials.input', ['name' => $name('passed_year'), 'key' => $key('passed_year'), 'id' => "q{$i}_year", 'label' => __('Passed year'), 'value' => $row['passed_year'] ?? null, 'col' => 'col-sm-4', 'placeholder' => __('e.g. 2075')])

            <div class="col-sm-4">
                <label class="form-label" for="q{{ $i }}_division">{{ __('Division / Grade') }}</label>
                <select class="form-select @error($key('division')) is-invalid @enderror" id="q{{ $i }}_division" name="{{ $name('division') }}">
                    <option value="">{{ __('Select') }}</option>
                    @foreach (['Distinction', 'First', 'Second', 'Third', 'Pass', 'Grade (GPA)'] as $division)
                        <option value="{{ $division }}" @selected(($row['division'] ?? null) === $division)>{{ __($division) }}</option>
                    @endforeach
                </select>
                @error($key('division')) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            @include('admin.pages.applicant.partials.input', ['name' => $name('percentage'), 'key' => $key('percentage'), 'id' => "q{$i}_percentage", 'label' => __('Percentage / GPA'), 'value' => $row['percentage'] ?? null, 'col' => 'col-sm-4', 'placeholder' => __('e.g. 72.5 or 3.6')])

            <div class="col-12">
                <label class="form-label" for="q{{ $i }}_documents">{{ __('Documents (transcript, character certificate, etc.)') }}</label>

                @if ($documents->isNotEmpty())
                    <div class="d-flex flex-wrap gap-3 mb-2">
                        @foreach ($documents as $document)
                            <div class="border rounded p-2 d-flex flex-column align-items-start gap-1">
                                @include('admin.pages.applicant.partials.media-link', ['media' => $document])
                                <label class="form-check-label small text-danger">
                                    <input type="checkbox" class="form-check-input me-1" name="{{ $name('remove_documents') }}[]" value="{{ $document->id }}">
                                    {{ __('Remove') }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif

                <input type="file" class="form-control @error($key('documents') . '.*') is-invalid @enderror" id="q{{ $i }}_documents"
                    name="{{ $name('documents') }}[]" multiple accept="image/jpeg,image/png,image/webp,application/pdf">
                @foreach ($errors->get($key('documents') . '.*') as $messages)
                    <div class="invalid-feedback d-block">{{ $messages[0] }}</div>
                @endforeach
                <div class="form-text">{{ __('You can select several files. JPG, PNG, WEBP or PDF, up to 2 MB each.') }}</div>
            </div>
        </div>
    </div>
</div>
