{{--
    Labelled text input. Needs $name, $label, $value; optional $type, $col, $key (error key,
    defaults to $name), $id, $placeholder, $hint, $list (datalist id), $lang.
--}}
@php
    $key = $key ?? $name;
    $id = $id ?? $name;
@endphp
<div class="{{ $col ?? 'col-md-6' }}">
    <label class="form-label" for="{{ $id }}">{{ $label }}</label>
    <input type="{{ $type ?? 'text' }}" class="form-control @error($key) is-invalid @enderror"
        id="{{ $id }}" name="{{ $name }}" value="{{ $value }}" maxlength="255"
        @isset($placeholder) placeholder="{{ $placeholder }}" @endisset
        @isset($list) list="{{ $list }}" @endisset
        @isset($lang) lang="{{ $lang }}" @endisset>
    @error($key) <div class="invalid-feedback">{{ $message }}</div> @enderror
    @isset($hint) <div class="form-text">{{ $hint }}</div> @endisset
</div>
