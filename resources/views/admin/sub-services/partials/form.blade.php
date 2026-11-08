@php
    $names = $names ?? collect();
    $descriptions = $descriptions ?? collect();
    $locale = app()->getLocale();
@endphp

<form method="post" enctype="multipart/form-data" action="{{ $action }}">
    @csrf
    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    {{-- Section: Basic Settings --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-sliders text-rounder-orange"></i>
            <span>{{ __('Basic settings') }}</span>
        </h6>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-screwdriver-wrench text-rounder-orange me-1"></i> {{ __('Service') }} <span class="text-danger">*</span>
                </label>
                <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" required style="border-radius: 10px;">
                    <option value="">{{ __('Choose service') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $subService?->service_id) == $service->id)>
                            {{ $service->translations->firstWhere('locale', $locale)?->name ?? $service->code }}
                        </option>
                    @endforeach
                </select>
                @error('service_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">
                    {{ __('Sub service code') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-barcode"></i>
                    </span>
                    <input name="code"
                           value="{{ old('code', $subService?->code) }}"
                           class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror"
                           required
                           maxlength="100"
                           pattern="[A-Za-z0-9_-]+"
                           placeholder="e.g. filter-clean, gas-charge"
                           style="border-radius: 10px 0 0 10px;">
                </div>
                @error('code')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-arrow-down-1-9 text-rounder-orange me-1"></i> {{ __('Sort order') }}
                </label>
                <input name="sort_order" type="number" min="0" value="{{ old('sort_order', $subService?->sort_order ?? 0) }}" class="form-control font-monospace @error('sort_order') is-invalid @enderror" style="border-radius: 10px;">
                @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-toggle-on text-rounder-orange me-1"></i> {{ __('Status') }}
                </label>
                <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                    <option value="1" @selected((int) old('is_active', $subService?->is_active ?? 1) === 1)>{{ __('Active') }}</option>
                    <option value="0" @selected((int) old('is_active', $subService?->is_active ?? 1) === 0)>{{ __('Inactive') }}</option>
                </select>
                @error('is_active')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary">
                    <i class="fa-regular fa-image text-rounder-orange me-1"></i> {{ __('Image') }}
                </label>
                <input name="image" type="file" accept="image/*" class="form-control @error('image') is-invalid @enderror" style="border-radius: 10px;">
                @if($subService?->image_path)
                    <div class="d-flex align-items-center gap-2 mt-2 p-2 border rounded-3 bg-light">
                        <img class="rounded-2 border" width="48" height="48" style="object-fit: cover;" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($subService->image_path) }}" alt="{{ __('Product image') }}">
                        <span class="small text-muted">{{ __('Current image') }}</span>
                    </div>
                @endif
                @error('image')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <hr class="my-4 text-muted opacity-25">

    {{-- Section: Multilingual Names & Descriptions --}}
    <div class="mb-4">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-language text-rounder-orange"></i>
            <span>{{ __('Multilingual names and descriptions') }}</span>
        </h6>

        <div class="row g-3">
            @php
                $subLangs = [
                    'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'placeholder' => 'مثال: تنظيف فلاتر التكييف', 'required' => true, 'dir' => 'rtl'],
                    'en' => ['label' => 'English', 'flag' => '🇬🇧', 'placeholder' => 'e.g. AC Filter Cleaning', 'required' => false, 'dir' => 'ltr'],
                    'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'placeholder' => 'مثال: اے سی فلٹر کی صفائی', 'required' => false, 'dir' => 'rtl'],
                ];
            @endphp

            @foreach($subLangs as $code => $meta)
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-secondary d-flex align-items-center justify-content-between">
                        <span>
                            <span class="me-1">{{ $meta['flag'] }}</span>
                            {{ __('Sub service name') }} ({{ $meta['label'] }})
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
                           name="name_{{ $code }}"
                           value="{{ old('name_'.$code, $names[$code] ?? '') }}"
                           class="form-control mb-2 @error('name_'.$code) is-invalid @enderror"
                           @required($meta['required'])
                           maxlength="255"
                           placeholder="{{ $meta['placeholder'] }}"
                           dir="{{ $meta['dir'] }}"
                           style="border-radius: 10px;">
                    @error('name_'.$code)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror

                    <label class="form-label small fw-semibold text-secondary mt-1">
                        {{ __('Description') }} · {{ $meta['label'] }}
                    </label>
                    <textarea name="description_{{ $code }}"
                              class="form-control @error('description_'.$code) is-invalid @enderror"
                              rows="3"
                              maxlength="2000"
                              placeholder="{{ __('Description') }} ({{ $meta['label'] }})"
                              dir="{{ $meta['dir'] }}"
                              style="border-radius: 10px;">{{ old('description_'.$code, $descriptions[$code] ?? '') }}</textarea>
                    @error('description_'.$code)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    {{-- Form Actions --}}
    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
        <a href="{{ route('admin.sub-services.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
            {{ __('Cancel') }}
        </a>
        <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
            <i class="fa-solid fa-check"></i>
            <span>{{ __('Save sub service') }}</span>
        </button>
    </div>
</form>
