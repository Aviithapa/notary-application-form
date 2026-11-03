{{-- Badge for where an exam's application window stands today. Needs $exam. --}}
@php
    $phase = $exam->phase();
    [$class, $label] = match (true) {
        $exam->status !== 'published' => ['bg-secondary-subtle text-secondary', $exam->statusLabel()],
        $phase === \App\Models\Exam::PHASE_UPCOMING => ['bg-info-subtle text-info', __('Opens soon')],
        $phase === \App\Models\Exam::PHASE_OPEN => ['bg-success-subtle text-success', __('Open')],
        $phase === \App\Models\Exam::PHASE_DOUBLE_FEE => ['bg-warning-subtle text-warning', __('Double fee period')],
        default => ['bg-danger-subtle text-danger', __('Closed')],
    };
@endphp
<span class="badge {{ $class }}">{{ $label }}</span>
