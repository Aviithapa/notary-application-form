{{-- One label/value pair on the application view. Needs $label, $value; optional $col. --}}
<div class="{{ $col ?? 'col-sm-6 col-lg-4' }}">
    <div class="text-muted small">{{ $label }}</div>
    <div class="fw-semibold text-break">{{ filled($value) ? $value : '—' }}</div>
</div>
