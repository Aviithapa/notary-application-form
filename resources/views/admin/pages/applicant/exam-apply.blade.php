@extends('admin.layout.app')

@php
    use App\Models\ExamApplication;
    $money = fn ($amount) => __('Rs.') . ' ' . number_format((float) $amount, 2);
    $deadlineBs = $doubleFee ? $exam->double_fee_end_bs : $exam->application_end_bs;
    $deadlineAd = $doubleFee ? $exam->double_fee_end_ad : $exam->application_end_ad;
@endphp

@section('content')
    @include('admin.component.breadcrumb', ['title' => __('Application form')])

    @if ($errors->any())
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-1"></i>
            {{ __('Please correct the highlighted fields below. Files you selected need to be chosen again.') }}
        </div>
    @endif

    <div class="row">
        {{-- Exam summary --}}
        <div class="col-xl-4 order-xl-2">
            <div class="card exam-apply-summary">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span class="exam-apply-icon"><i class="bi {{ $exam->service->icon }}"></i></span>
                        <div>
                            <h5 class="mb-0">{{ $exam->localized('title') }}</h5>
                            <div class="text-muted small">{{ $exam->service->localized('name') }}</div>
                        </div>
                    </div>

                    <dl class="row small mb-0">
                        <dt class="col-6 text-muted fw-normal">{{ __('Last date') }}</dt>
                        <dd class="col-6 text-end mb-2">{{ $deadlineBs }}<div class="text-muted">{{ $deadlineAd }}</div></dd>

                        @if ($exam->exam_date_bs)
                            <dt class="col-6 text-muted fw-normal">{{ __('Exam date') }}</dt>
                            <dd class="col-6 text-end mb-2">{{ $exam->exam_date_bs }}
                                @if ($exam->start_time) <div class="text-muted">{{ substr($exam->start_time, 0, 5) }}</div> @endif
                            </dd>
                        @endif

                        <dt class="col-6 text-muted fw-normal border-top pt-2">{{ __('Fee to pay') }}</dt>
                        <dd class="col-6 text-end border-top pt-2 mb-0">
                            <strong class="fs-5" style="color: #003893;">{{ $fee === null ? __('Free') : $money($fee) }}</strong>
                            @if ($doubleFee)
                                <div><span class="badge bg-warning-subtle text-warning">{{ __('Double fee') }}</span></div>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <div class="col-xl-8 order-xl-1">
            <form action="{{ route('applicant.exams.apply', $exam) }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf

                @if ($fee !== null)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="applicant-section-title"><i class="bi bi-receipt me-1"></i> {{ __('Payment details') }}</h5>
                            <p class="text-muted small">
                                {{ __('Deposit the fee at the bank and upload a clear photo or scan of the deposit voucher.') }}
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="paid_amount">{{ __('Amount paid') }} *</label>
                                    <div class="input-group has-validation">
                                        <span class="input-group-text">{{ __('Rs.') }}</span>
                                        <input type="number" step="0.01" min="{{ $fee }}" class="form-control @error('paid_amount') is-invalid @enderror"
                                            id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $fee) }}" required>
                                        @error('paid_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="form-text">{{ __('Amount shown on your bank voucher.') }}</div>
                                </div>

                                <div class="col-md-6">
                                    @include('admin.pages.applicant.partials.upload-preview', [
                                        'name' => ExamApplication::VOUCHER,
                                        'label' => __('Bank voucher') . ' *',
                                        'accept' => 'image/jpeg,image/png,image/webp,application/pdf',
                                        'hint' => __('JPG, PNG, WEBP or PDF, up to 2 MB.'),
                                        'icon' => 'bi-receipt',
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <h5 class="applicant-section-title"><i class="bi bi-fingerprint me-1"></i> {{ __('Signature and thumbprints') }}</h5>
                        <p class="text-muted small">
                            {{ __('Sign and press your thumbs on white paper, then upload a clear photo of each.') }}
                        </p>
                        <div class="row g-3">
                            @foreach (ExamApplication::IDENTITY_MARKS as $collection => $label)
                                <div class="col-md-4">
                                    @include('admin.pages.applicant.partials.upload-preview', [
                                        'name' => $collection,
                                        'label' => __($label) . ' *',
                                        'accept' => 'image/jpeg,image/png,image/webp',
                                        'hint' => __('JPG, PNG or WEBP, up to 2 MB.'),
                                        'icon' => $collection === ExamApplication::SIGNATURE ? 'bi-pen' : 'bi-fingerprint',
                                    ])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input @error('declaration') is-invalid @enderror" type="checkbox" id="declaration" name="declaration" value="1" @checked(old('declaration'))>
                            <label class="form-check-label" for="declaration">
                                {{ __('I declare that the details and documents I have submitted are true and correct.') }}
                            </label>
                            @error('declaration') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="button" class="btn btn-link btn-sm px-0 mt-1" data-bs-toggle="modal" data-bs-target="#application-details">
                            <i class="bi bi-eye me-1"></i>{{ __('View details') }}
                        </button>
                        <span class="text-muted small">— {{ __('check the personal details, qualifications and trainings you are submitting.') }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between gap-2 mb-3">
                    <a href="{{ route('applicant.exams') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send me-1"></i> {{ __('Submit application') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Everything the applicant is declaring, to review before submitting --}}
    <div class="modal fade" id="application-details" tabindex="-1" aria-labelledby="application-details-title" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-lg-down">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="application-details-title">{{ __('Your application details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body bg-light">
                    @include('admin.pages.applications.partials.profile', ['user' => $user])
                </div>
                <div class="modal-footer justify-content-between">
                    <a href="{{ route('applicant.personal') }}" class="btn btn-light"><i class="bi bi-pencil me-1"></i> {{ __('Edit details') }}</a>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: #003893; border-color: #003893;">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .applicant-section-title { color: #003893; font-weight: 600; border-bottom: 1px solid #eef1f7; padding-bottom: .5rem; margin-bottom: 1rem; }
        .exam-apply-summary { border-top: 3px solid #003893; }
        @media (min-width: 1200px) { .exam-apply-summary { position: sticky; top: 90px; } }
        .exam-apply-icon {
            display: inline-flex; align-items: center; justify-content: center; flex: none;
            width: 44px; height: 44px; border-radius: 12px;
            background: rgba(0, 56, 147, .08); color: #003893; font-size: 1.35rem;
        }
        .upload-preview {
            height: 140px; border: 1px dashed #c7d0e3; border-radius: 8px; background: #f8f9fc;
            display: flex; align-items: center; justify-content: center; overflow: hidden; color: #98a6c2;
        }
        .upload-preview img { max-width: 100%; max-height: 100%; object-fit: contain; }
    </style>
@endpush

@push('scripts')
    <script>
        // Show a preview of the chosen image (or the file name for a PDF) before submitting
        document.querySelectorAll('[data-upload-preview]').forEach((input) => {
            const box = document.getElementById(input.dataset.uploadPreview);
            const empty = box.innerHTML;
            let url = null;

            input.addEventListener('change', () => {
                if (url) URL.revokeObjectURL(url);
                const file = input.files[0];
                if (!file) {
                    box.innerHTML = empty;
                    return;
                }
                if (file.type.startsWith('image/')) {
                    url = URL.createObjectURL(file);
                    const img = document.createElement('img');
                    img.src = url;
                    img.alt = file.name;
                    box.replaceChildren(img);
                } else {
                    const label = document.createElement('span');
                    label.className = 'small text-center px-2';
                    label.textContent = file.name;
                    box.replaceChildren(label);
                }
            });
        });
    </script>
@endpush
