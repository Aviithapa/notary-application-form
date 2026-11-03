{{-- Delete button for a service. Needs $service; optional $compact (icon only) and $class. --}}
<form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline {{ $class ?? '' }}"
    onsubmit="return confirm(@js(__('Delete this service?')))">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
        <i class="bi bi-trash"></i>@empty($compact) {{ __('Delete') }}@endempty
    </button>
</form>
