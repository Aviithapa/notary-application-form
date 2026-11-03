@extends('admin.layout.app')

@section('content')
    @include('admin.pages.applicant.partials.steps')

    <form action="{{ route('applicant.qualifications.save') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        {{-- Read-only once an application has been approved; the server refuses saves too --}}
        <fieldset @disabled($locked) class="applicant-fieldset">

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 applicant-section-title">
                    <h5 class="m-0"><i class="bi bi-mortarboard me-1"></i> {{ __('Academic qualifications') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="qualification-template" data-rows="qualification-rows">
                        <i class="bi bi-plus-lg"></i> {{ __('Add qualification') }}
                    </button>
                </div>
                <p class="text-muted small">{{ __('Add each degree or certificate you have passed, starting with the highest.') }}</p>

                <div class="d-flex flex-column gap-3 applicant-rows" id="qualification-rows">
                    @foreach ($rows as $i => $row)
                        @include('admin.pages.applicant.partials.qualification-row', [
                            'i' => $i,
                            'row' => $row,
                            'model' => isset($row['id']) ? $saved->get((int) $row['id']) : null,
                        ])
                    @endforeach
                </div>
            </div>
        </div>

        <datalist id="qualification-levels">
            @foreach (['SLC / SEE', 'Intermediate / +2', 'Bachelor', 'Bachelor of Laws (LLB)', 'Master', 'Master of Laws (LLM)', 'M.Phil', 'PhD'] as $level)
                <option value="{{ __($level) }}"></option>
            @endforeach
        </datalist>

        <template id="qualification-template">
            @include('admin.pages.applicant.partials.qualification-row', ['i' => '__INDEX__', 'row' => [], 'model' => null])
        </template>

        </fieldset>

        <div class="applicant-actions d-flex justify-content-between gap-2">
            <a href="{{ route('applicant.personal') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
            @if ($locked)
                <a href="{{ route('applicant.trainings') }}" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                    {{ __('Next') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @else
                <button type="submit" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                    {{ __('Save and continue') }} <i class="bi bi-arrow-right ms-1"></i>
                </button>
            @endif
        </div>
    </form>
@endsection

@include('admin.pages.applicant.partials.scripts')
