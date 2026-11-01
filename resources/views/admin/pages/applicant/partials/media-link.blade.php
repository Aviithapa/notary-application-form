{{-- Link to an uploaded file: a thumbnail for images, an icon and file name otherwise. Needs $media. --}}
<a href="{{ route('lookup.media', $media) }}" target="_blank" rel="noopener"
    class="d-inline-flex align-items-center gap-2 text-decoration-none" title="{{ $media->file_name }}">
    @if (str_starts_with((string) $media->mime_type, 'image/'))
        <img src="{{ route('lookup.media', $media) }}" alt="{{ $media->file_name }}" class="rounded border applicant-thumb" loading="lazy">
    @else
        <i class="bi {{ $media->mime_type === 'application/pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark' }} fs-3"></i>
        <span class="small text-truncate" style="max-width: 160px;">{{ $media->file_name }}</span>
    @endif
</a>
