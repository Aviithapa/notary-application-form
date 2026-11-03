{{--
    Link to an uploaded file: a thumbnail for images, an icon and file name otherwise. Needs $media;
    optional $caption. Images are tagged for the image viewer (data-gallery-item) where it is included.
--}}
@php $isImage = str_starts_with((string) $media->mime_type, 'image/'); @endphp
<a href="{{ route('lookup.media', $media) }}" target="_blank" rel="noopener"
    class="d-inline-flex align-items-center gap-2 text-decoration-none" title="{{ $caption ?? $media->file_name }}"
    @if ($isImage) data-gallery-item data-caption="{{ $caption ?? $media->file_name }}" @endif>
    @if ($isImage)
        <img src="{{ route('lookup.media', $media) }}" alt="{{ $caption ?? $media->file_name }}" class="rounded border applicant-thumb" loading="lazy">
    @else
        <i class="bi {{ $media->mime_type === 'application/pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark' }} fs-3"></i>
        <span class="small text-truncate" style="max-width: 160px;">{{ $media->file_name }}</span>
    @endif
</a>
