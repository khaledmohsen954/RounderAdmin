@php
    $names = $names ?? collect();
    $locale = app()->getLocale();
@endphp

<form method="post" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <input type="hidden" name="country_code" value="{{ old('country_code', $region?->country_code?->value ?? $region?->country_code ?? 'SA') }}">
    @if($region?->code)
        <input type="hidden" name="code" value="{{ $region->code }}">
    @endif

    {{-- Section: Basic Information --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-sliders text-rounder-orange"></i>
            <span>{{ __('Basic settings') }}</span>
        </h6>
        <div class="row g-3">
            @if(!$region?->code)
            <div class="col-md-6 col-lg-4">
                <label class="form-label small fw-semibold text-secondary">
                    {{ __('Region code') }} <span class="text-muted fw-normal">({{ __('Optional') }})</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-barcode"></i>
                    </span>
                    <input name="code" value="{{ old('code', $region?->code) }}" class="form-control border-start-0 @error('code') is-invalid @enderror" maxlength="60" pattern="[A-Za-z0-9_-]+" placeholder="e.g. riyadh, makkah" style="border-radius: 10px 0 0 10px;">
                    @error('code')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <small class="text-muted d-block mt-1">{{ __('Unique slug/identifier. Generated automatically if left blank.') }}</small>
            </div>
            @endif

            <div class="col-md-6 col-lg-4">
                <label class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-toggle-on text-rounder-orange me-1"></i> {{ __('Status') }}
                </label>
                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                    <option value="1" @selected((int) old('is_active', $region?->is_active ?? 1) === 1)>{{ __('Active') }}</option>
                    <option value="0" @selected((int) old('is_active', $region?->is_active ?? 1) === 0)>{{ __('Inactive') }}</option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <hr class="my-4 text-muted opacity-25">

    {{-- Section: Multilingual Names --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-language text-rounder-orange"></i>
            <span>{{ __('Multilingual names') }}</span>
        </h6>
        <div class="row g-3">
            @php
                $languages = [
                    'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'placeholder' => 'مثال: منطقة الرياض', 'required' => true],
                    'en' => ['label' => 'English', 'flag' => '🇬🇧', 'placeholder' => 'e.g. Riyadh Region', 'required' => false],
                    'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'placeholder' => 'مثال: ریاض علاقہ', 'required' => false],
                ];
            @endphp

            @foreach($languages as $langCode => $meta)
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-secondary d-flex align-items-center justify-content-between">
                        <span>
                            <span class="me-1">{{ $meta['flag'] }}</span>
                            {{ __('Region name') }} ({{ $meta['label'] }})
                            @if($meta['required'])
                                <span class="text-danger">*</span>
                            @endif
                        </span>
                        @if($meta['required'])
                            <span class="badge bg-danger-subtle text-danger" style="font-size: 0.7rem;">{{ __('Required') }}</span>
                        @else
                            <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">{{ __('Optional') }}</span>
                        @endif
                    </label>
                    <input type="text"
                           name="name_{{ $langCode }}"
                           value="{{ old('name_'.$langCode, $names[$langCode] ?? '') }}"
                           class="form-control @error('name_'.$langCode) is-invalid @enderror"
                           @required($meta['required'])
                           maxlength="120"
                           placeholder="{{ $meta['placeholder'] }}"
                           style="border-radius: 10px;"
                           dir="{{ in_array($langCode, ['ar', 'ur']) ? 'rtl' : 'ltr' }}">
                    @error('name_'.$langCode)
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    {{-- Form Actions --}}
    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
        <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
            <i class="fa-solid fa-check"></i>
            <span>{{ __('Save') }}</span>
        </button>
    </div>
</form>
