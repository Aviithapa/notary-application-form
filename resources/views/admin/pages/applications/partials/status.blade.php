{{-- Status badge of an exam application. Needs $application. --}}
<span class="badge {{ $application->statusClass() }}">{{ $application->statusLabel() }}</span>
