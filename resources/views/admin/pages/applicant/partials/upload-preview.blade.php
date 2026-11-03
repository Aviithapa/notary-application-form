{{--
    File input with a preview box above it. Needs $name, $label, $accept, $hint, $icon; optional
    $existing (media already uploaded: shown in the box, and the input becomes optional).
--}}
@php $existing ??= null; @endphp
<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<div class="upload-preview mb-2" id="{{ $name }}_preview">
    @if ($existing && str_starts_with((string) $existing->mime_type, 'image/'))
        <img src="{{ route('lookup.media', $existing) }}" alt="{{ $label }}">
    @elseif ($existing)
        <span class="small text-center px-2"><i class="bi bi-file-earmark-pdf text-danger fs-3 d-block"></i>{{ $existing->file_name }}</span>
    @else
        <i class="bi {{ $icon }}" style="font-size: 2.25rem;" aria-hidden="true"></i>
    @endif
</div>
<input type="file" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}"
    accept="{{ $accept }}" data-upload-preview="{{ $name }}_preview" @unless ($existing) required @endunless>
@error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
<div class="form-text">{{ $existing ? __('Leave empty to keep the current file.') : $hint }}</div>
