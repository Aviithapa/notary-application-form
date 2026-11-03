{{-- File input with a preview box above it. Needs $name, $label, $accept, $hint, $icon. --}}
<label class="form-label" for="{{ $name }}">{{ $label }}</label>
<div class="upload-preview mb-2" id="{{ $name }}_preview" aria-hidden="true">
    <i class="bi {{ $icon }}" style="font-size: 2.25rem;"></i>
</div>
<input type="file" class="form-control @error($name) is-invalid @enderror" id="{{ $name }}" name="{{ $name }}"
    accept="{{ $accept }}" data-upload-preview="{{ $name }}_preview" required>
@error($name) <div class="invalid-feedback">{{ $message }}</div> @enderror
<div class="form-text">{{ $hint }}</div>
