{{-- Application window of an exam as compact lines (B.S. first, A.D. below). Needs $exam. --}}
<div class="small">
    <div><span class="text-muted">{{ __('From') }}:</span> {{ $exam->application_start_bs }} <span class="text-muted">({{ $exam->application_start_ad }})</span></div>
    <div><span class="text-muted">{{ __('To') }}:</span> {{ $exam->application_end_bs }} <span class="text-muted">({{ $exam->application_end_ad }})</span></div>
    @if ($exam->double_fee_end_ad)
        <div><span class="text-muted">{{ __('Double fee until') }}:</span> {{ $exam->double_fee_end_bs }} <span class="text-muted">({{ $exam->double_fee_end_ad }})</span></div>
    @endif
</div>
