{{-- Printable admit card (प्रवेश पत्र), A4. Needs $application with loadFullDetails(). --}}
@php
    use App\Models\ApplicantProfile;
    use App\Models\ExamApplication;

    $exam = $application->exam;
    $profile = $application->user->applicantProfile;
    $nepali = app()->getLocale() === 'np';
    $photo = $profile?->getFirstMedia(ApplicantProfile::PHOTO);
    $date = fn ($bs, $ad) => $bs ? "$bs " . __('B.S.') . " ($ad)" : '—';
@endphp
<!DOCTYPE html>
<html lang="{{ $nepali ? 'ne' : 'en' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Admit card') }} · {{ $application->rollNumberLabel() }} · {{ __('Nepal Notary Public Council') }}</title>
    <link rel="shortcut icon" href="{{ asset('frontend/images/logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --brand: #003893; --line: #c9d2e3; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #e9edf4; font-family: 'Segoe UI', Roboto, 'Noto Sans Devanagari', Arial, sans-serif; color: #1f2937; }
        .toolbar { display: flex; justify-content: center; gap: .5rem; padding: 1rem; }
        .toolbar button, .toolbar a { border: 0; border-radius: 8px; padding: .55rem 1.1rem; font-size: .95rem; cursor: pointer; text-decoration: none; }
        .toolbar .primary { background: var(--brand); color: #fff; }
        .toolbar .light { background: #fff; color: #1f2937; }
        .sheet { width: 210mm; min-height: 148mm; margin: 0 auto 2rem; background: #fff; padding: 12mm 14mm; border-top: 6px solid var(--brand); box-shadow: 0 6px 24px rgba(0, 0, 0, .08); }
        .head { display: flex; align-items: center; gap: 14px; border-bottom: 2px solid var(--brand); padding-bottom: 10px; }
        .head img { height: 74px; width: auto; }
        .head .org { flex: 1; text-align: center; }
        .head .org .np { font-size: 20px; font-weight: 700; color: var(--brand); }
        .head .org .en { font-size: 15px; font-weight: 600; letter-spacing: .02em; }
        .head .org .sub { font-size: 12px; color: #6b7280; }
        .title { text-align: center; margin: 12px 0 4px; font-size: 18px; font-weight: 700; letter-spacing: .05em; }
        .title small { display: block; font-size: 13px; font-weight: 500; color: #4b5563; letter-spacing: 0; }
        .body { display: flex; gap: 16px; margin-top: 12px; }
        .details { flex: 1; border-collapse: collapse; font-size: 13.5px; }
        .details th, .details td { border: 1px solid var(--line); padding: 6px 9px; text-align: left; vertical-align: top; }
        .details th { width: 34%; background: #f4f6fb; font-weight: 600; }
        .roll { font-size: 20px; font-weight: 800; color: var(--brand); letter-spacing: .08em; }
        .side { width: 38mm; display: flex; flex-direction: column; gap: 8px; }
        .box { border: 1px solid var(--line); display: flex; align-items: center; justify-content: center; overflow: hidden; background: #fff; }
        .box img { width: 100%; height: 100%; object-fit: contain; }
        .photo { height: 45mm; }
        .photo img { object-fit: cover; }
        .caption { font-size: 10.5px; text-align: center; color: #4b5563; margin-top: 2px; }
        .marks { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-top: 14px; }
        .marks .box { height: 22mm; }
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 22px; font-size: 12.5px; }
        .sign-line { width: 60mm; border-top: 1px solid #111; text-align: center; padding-top: 4px; }
        .rules { margin-top: 14px; font-size: 11.5px; color: #374151; border-top: 1px dashed var(--line); padding-top: 8px; }
        .rules ol { margin: 4px 0 0; padding-left: 18px; }
        @page { size: A4; margin: 8mm; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; width: auto; padding: 0; border-top-width: 4px; }
        }
        @media (max-width: 820px) {
            .sheet { width: auto; margin: 0 8px 1rem; padding: 16px; }
            .body { flex-direction: column-reverse; }
            .side { width: 100%; flex-direction: row; }
            .side > div { flex: 1; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="primary" onclick="window.print()"><i class="bi bi-printer"></i> {{ __('Print admit card') }}</button>
        <a href="javascript:history.back()" class="light"><i class="bi bi-arrow-left"></i> {{ __('Back') }}</a>
    </div>

    <div class="sheet">
        <div class="head">
            <img src="{{ asset('frontend/images/logo.png') }}" alt="">
            <div class="org">
                <div class="np">नेपाल नोटरी पब्लिक परिषद्</div>
                <div class="en">NEPAL NOTARY PUBLIC COUNCIL</div>
                <div class="sub">{{ __('Kathmandu, Nepal') }}</div>
            </div>
            <img src="{{ asset('frontend/images/logo.png') }}" alt="" style="visibility: hidden;">
        </div>

        <div class="title">
            {{ __('ADMIT CARD') }} / प्रवेश पत्र
            <small>{{ $exam->localized('title') }} · {{ $exam->service->localized('name') }}</small>
        </div>

        <div class="body">
            <table class="details">
                <tr><th>{{ __('Roll no.') }}</th><td class="roll">{{ $application->rollNumberLabel() }}</td></tr>
                <tr><th>{{ __('Application no.') }}</th><td>{{ $application->application_number }}</td></tr>
                <tr><th>{{ __('Name') }}</th><td><strong>{{ $profile?->name ?: '—' }}</strong>@if ($profile?->name_np) <div>{{ $profile->name_np }}</div> @endif</td></tr>
                <tr><th>{{ __('Date of birth') }}</th><td>{{ $date($profile?->dob_bs, $profile?->dob_ad) }}</td></tr>
                <tr><th>{{ __('Citizenship number') }}</th><td>{{ $profile?->citizenship_number ?: '—' }}</td></tr>
                <tr><th>{{ __("Father's name") }}</th><td>{{ collect([$profile?->father_name, $profile?->father_name_np])->filter()->implode(' / ') ?: '—' }}</td></tr>
                <tr><th>{{ __('Exam date') }}</th><td><strong>{{ $date($exam->exam_date_bs, $exam->exam_date_ad) }}</strong></td></tr>
                <tr><th>{{ __('Time') }}</th><td>
                    {{ $exam->start_time ? substr($exam->start_time, 0, 5) : '—' }}
                    @if ($exam->duration_minutes) ({{ $exam->duration_minutes }} {{ __('min') }}) @endif
                </td></tr>
                <tr><th>{{ __('Venue') }}</th><td>{{ $exam->localized('venue') ?: __('To be announced') }}</td></tr>
            </table>

            <div class="side">
                <div>
                    <div class="box photo">
                        @if ($photo) <img src="{{ route('lookup.media', $photo) }}" alt="{{ __('Photo') }}"> @endif
                    </div>
                    <div class="caption">{{ __('Photo') }}</div>
                </div>
            </div>
        </div>

        <div class="marks">
            @foreach (ExamApplication::IDENTITY_MARKS as $collection => $label)
                <div>
                    <div class="box">
                        @if ($file = $application->getFirstMedia($collection)) <img src="{{ route('lookup.media', $file) }}" alt="{{ __($label) }}"> @endif
                    </div>
                    <div class="caption">{{ __("Candidate's") }} {{ __($label) }}</div>
                </div>
            @endforeach
        </div>

        <div class="footer">
            <div>{{ __('Issued on') }}: {{ $application->admit_card_generated_at?->setTimezone(config('app.business_timezone'))->format('Y-m-d') }}</div>
            <div class="sign-line">{{ __('Authorised signature') }}</div>
        </div>

        <div class="rules">
            <strong>{{ __('Instructions') }}</strong>
            <ol>
                <li>{{ __('Bring this admit card and your citizenship certificate to the exam hall.') }}</li>
                <li>{{ __('Arrive at the venue at least 30 minutes before the exam starts.') }}</li>
                <li>{{ __('Mobile phones and electronic devices are not allowed in the exam hall.') }}</li>
            </ol>
        </div>
    </div>
</body>
</html>
