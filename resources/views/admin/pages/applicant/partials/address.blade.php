{{--
    District -> municipality -> ward -> tole selects that load their options from the one above.
    Needs: $prefix ('' or 'correspondence_'), $districts, $municipalities, $wards,
           $values (district_id, municipality_id, ward_id, tole).
--}}
@php $nepali = app()->getLocale() === 'np'; @endphp

<div class="row g-3" data-location-group>
    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $prefix }}district_id">{{ __('District') }}</label>
        <select class="form-select @error($prefix . 'district_id') is-invalid @enderror"
            id="{{ $prefix }}district_id" name="{{ $prefix }}district_id"
            data-location="district" data-children-url="{{ route('lookup.municipalities', '__ID__') }}">
            <option value="">{{ __('Select district') }}</option>
            @foreach ($districts as $district)
                <option value="{{ $district->id }}" @selected((string) $district->id === (string) $values['district_id'])>
                    {{ $nepali ? $district->name_np : $district->name_en }}
                </option>
            @endforeach
        </select>
        @error($prefix . 'district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $prefix }}municipality_id">{{ __('Municipality') }}</label>
        <select class="form-select @error($prefix . 'municipality_id') is-invalid @enderror"
            id="{{ $prefix }}municipality_id" name="{{ $prefix }}municipality_id"
            data-location="municipality" data-children-url="{{ route('lookup.wards', '__ID__') }}"
            data-placeholder="{{ __('Select municipality') }}" @disabled($municipalities->isEmpty())>
            <option value="">{{ __('Select municipality') }}</option>
            @foreach ($municipalities as $municipality)
                <option value="{{ $municipality->id }}" @selected((string) $municipality->id === (string) $values['municipality_id'])>
                    {{ $nepali ? $municipality->name_np : $municipality->name_en }}
                </option>
            @endforeach
        </select>
        @error($prefix . 'municipality_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $prefix }}ward_id">{{ __('Ward no.') }}</label>
        <select class="form-select @error($prefix . 'ward_id') is-invalid @enderror"
            id="{{ $prefix }}ward_id" name="{{ $prefix }}ward_id"
            data-location="ward" data-placeholder="{{ __('Select ward') }}" @disabled($wards->isEmpty())>
            <option value="">{{ __('Select ward') }}</option>
            @foreach ($wards as $ward)
                <option value="{{ $ward->id }}" @selected((string) $ward->id === (string) $values['ward_id'])>{{ $ward->ward_number }}</option>
            @endforeach
        </select>
        @error($prefix . 'ward_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-sm-6 col-lg-3">
        <label class="form-label" for="{{ $prefix }}tole">{{ __('Tole') }}</label>
        <input type="text" class="form-control @error($prefix . 'tole') is-invalid @enderror"
            id="{{ $prefix }}tole" name="{{ $prefix }}tole" value="{{ $values['tole'] }}" maxlength="255">
        @error($prefix . 'tole') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
