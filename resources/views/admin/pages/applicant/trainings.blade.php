@extends('admin.layout.app')

@section('content')
    @include('admin.pages.applicant.partials.steps')

    <form action="{{ route('applicant.trainings.save') }}" method="POST" enctype="multipart/form-data" novalidate>
        @csrf
        {{-- Read-only once an application has been approved; the server refuses saves too --}}
        <fieldset @disabled($locked) class="applicant-fieldset">

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 applicant-section-title">
                    <h5 class="m-0"><i class="bi bi-award me-1"></i> {{ __('Trainings') }}</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="training-template" data-rows="training-rows">
                        <i class="bi bi-plus-lg"></i> {{ __('Add training') }}
                    </button>
                </div>
                <p class="text-muted small">{{ __('Add any trainings you have completed. Leave this empty if you have none.') }}</p>

                <div class="d-flex flex-column gap-3 applicant-rows" id="training-rows">
                    @foreach ($rows as $i => $row)
                        @include('admin.pages.applicant.partials.training-row', [
                            'i' => $i,
                            'row' => $row,
                            'model' => isset($row['id']) ? $saved->get((int) $row['id']) : null,
                        ])
                    @endforeach
                </div>
            </div>
        </div>

        <template id="training-template">
            @include('admin.pages.applicant.partials.training-row', ['i' => '__INDEX__', 'row' => [], 'model' => null])
        </template>

        </fieldset>

        <div class="applicant-actions d-flex justify-content-between gap-2">
            <a href="{{ route('applicant.qualifications') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
            @if ($locked)
                <a href="{{ route('applicant.applications') }}" class="btn btn-primary" style="background-color: #003893; border-color: #003893;">
                    {{ __('My applications') }} <i class="bi bi-arrow-right ms-1"></i>
                </a>
            @else
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-circle me-1"></i> {{ __('Save application') }}
                </button>
            @endif
        </div>
    </form>
@endsection

@include('admin.pages.applicant.partials.scripts')
