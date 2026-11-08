@extends('layouts.admin')
@section('title', __('Edit service') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.services.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Available services') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit service') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-rounder-orange"></i>
                <span>{{ __('Edit service') }}: {{ $translations->get(app()->getLocale())?->name ?? $service->code }}</span>
                <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">{{ $service->code }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update main service details, pricing structure, category, and translations.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.packages.services.update', 'PUT')
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.services.update', $service->id) }}">
            @csrf
            @method('PUT')

            {{-- 1. Basic Settings --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                    <span>{{ __('Basic settings') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="category" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-layer-group text-rounder-orange me-1"></i> {{ __('Category') }} <span class="text-danger">*</span>
                        </label>
                        <select id="category" name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror" required style="border-radius: 10px;">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('service_category_id', $service->service_category_id) == $category->id)>
                                    {{ $category->name ?? $category->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="code" class="form-label small fw-semibold text-secondary">
                            {{ __('Service code') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input id="code" name="code" value="{{ old('code', $service->code) }}" class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror" required maxlength="60" style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="icon" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-icons text-rounder-orange me-1"></i> {{ __('Icon') }}
                        </label>
                        <select id="icon" name="icon" class="form-select @error('icon') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="">{{ __('Default / Auto') }}</option>
                            <option value="ac" @selected(old('icon', $service->icon) == 'ac')>❄️ {{ __('Air conditioner (ac)') }}</option>
                            <option value="drop" @selected(old('icon', $service->icon) == 'drop')>💧 {{ __('Plumbing (drop)') }}</option>
                            <option value="bolt" @selected(old('icon', $service->icon) == 'bolt')>⚡ {{ __('Electricity (bolt)') }}</option>
                            <option value="spark" @selected(old('icon', $service->icon) == 'spark')>✨ {{ __('Cleaning (spark)') }}</option>
                            <option value="washer" @selected(old('icon', $service->icon) == 'washer')>🧺 {{ __('Appliances (washer)') }}</option>
                            <option value="paint" @selected(old('icon', $service->icon) == 'paint')>🎨 {{ __('Paint (paint)') }}</option>
                            <option value="tool" @selected(old('icon', $service->icon) == 'tool')>🔧 {{ __('Tools & Furniture (tool)') }}</option>
                            <option value="home" @selected(old('icon', $service->icon) == 'home')>🏠 {{ __('General home (home)') }}</option>
                        </select>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label for="price" class="form-label small fw-semibold text-secondary">
                            {{ __('Customer price before discount') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </span>
                            <input id="price" name="base_price" type="number" min="0" max="9999999999.99" step=".01" class="form-control border-start-0 font-monospace @error('base_price') is-invalid @enderror" value="{{ old('base_price', $service->price_before_discount ?? $service->base_price) }}" required style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('base_price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label for="active" class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-toggle-on text-rounder-orange me-1"></i> {{ __('Status') }}
                        </label>
                        <select id="active" name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="1" @selected(old('is_active', $service->is_active) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('is_active', $service->is_active) == 0)>{{ __('Inactive') }}</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pricing and Discount Fields --}}
                    @include('admin.packages.partials.pricing', ['item' => $service, 'priceField' => 'base_price'])
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Multilingual Names & Descriptions --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-language text-rounder-orange"></i>
                    <span>{{ __('Multilingual names and descriptions') }}</span>
                </h6>

                <div class="row g-3">
                    @php
                        $serviceLangs = [
                            'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl'],
                            'en' => ['label' => 'English', 'flag' => '🇬🇧', 'dir' => 'ltr'],
                            'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'dir' => 'rtl'],
                        ];
                    @endphp

                    @foreach($serviceLangs as $locale => $meta)
                        <div class="col-12 col-md-4">
                            <label for="name_{{ $locale }}" class="form-label small fw-semibold text-secondary">
                                <span class="me-1">{{ $meta['flag'] }}</span> {{ __('Service name') }} · {{ $meta['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input id="name_{{ $locale }}"
                                   name="name_{{ $locale }}"
                                   class="form-control mb-2 @error('name_'.$locale) is-invalid @enderror"
                                   dir="{{ $meta['dir'] }}"
                                   value="{{ old('name_'.$locale, $translations->get($locale)?->name) }}"
                                   required
                                   maxlength="160"
                                   style="border-radius: 10px;">
                            @error('name_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label for="description_{{ $locale }}" class="form-label small fw-semibold text-secondary mt-1">
                                {{ __('Description') }} · {{ $meta['label'] }}
                            </label>
                            <textarea id="description_{{ $locale }}"
                                      name="description_{{ $locale }}"
                                      class="form-control @error('description_'.$locale) is-invalid @enderror"
                                      rows="3"
                                      maxlength="5000"
                                      dir="{{ $meta['dir'] }}"
                                      style="border-radius: 10px;">{{ old('description_'.$locale, $translations->get($locale)?->description) }}</textarea>
                            @error('description_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="p-3 bg-light-subtle rounded-3 border d-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-circle-info text-primary"></i>
                <span class="small text-secondary">{{ __('Service changes apply to new orders. Existing order prices stay unchanged.') }}</span>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save service') }}</span>
                </button>
            </div>
        </form>
        @endadminCan
    </div>
</main>
@endsection
