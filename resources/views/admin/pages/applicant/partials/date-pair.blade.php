{{--
    B.S. and A.D. inputs that fill each other in. Needs: $label, $idPrefix,
    $bsName/$bsKey/$bsValue and $adName/$adKey/$adValue ($*Key is the validation error key),
    optional $maxToday (bool).
--}}
<div class="col-12" data-date-pair>
    <label class="form-label mb-1">{{ $label }}</label>
    <div class="row g-2">
        <div class="col-sm-6">
            <div class="input-group has-validation">
                <span class="input-group-text">{{ __('B.S.') }}</span>
                <input type="text" class="form-control @error($bsKey) is-invalid @enderror"
                    id="{{ $idPrefix }}_bs" name="{{ $bsName }}" value="{{ $bsValue }}"
                    placeholder="{{ __('YYYY-MM-DD') }}" maxlength="10" inputmode="numeric" autocomplete="off"
                    data-calendar="bs" aria-label="{{ $label }} ({{ __('B.S.') }})">
                @error($bsKey) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
        <div class="col-sm-6">
            <div class="input-group has-validation">
                <span class="input-group-text">{{ __('A.D.') }}</span>
                <input type="date" class="form-control @error($adKey) is-invalid @enderror"
                    id="{{ $idPrefix }}_ad" name="{{ $adName }}" value="{{ $adValue }}"
                    @if (! empty($maxToday)) max="{{ now()->toDateString() }}" @endif
                    data-calendar="ad" aria-label="{{ $label }} ({{ __('A.D.') }})">
                @error($adKey) <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>
    <div class="form-text" data-date-status>{{ __('Enter either date and the other is filled in automatically.') }}</div>
</div>
