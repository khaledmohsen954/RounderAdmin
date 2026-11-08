@extends('layouts.admin')
@section('title', __('Create service') . ' · Rounder')
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
                <span class="text-secondary small fw-semibold">{{ __('Create service') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-rounder-orange"></i>
                <span>{{ __('Create service') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Add a new main service, its pricing, category, and multilingual descriptions.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.packages.services.store', 'POST')
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.services.store') }}">
            @csrf

            {{-- 1. Basic Settings --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                    <span>{{ __('Basic settings') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-layer-group text-rounder-orange me-1"></i> {{ __('Category') }} <span class="text-danger">*</span>
                        </label>
                        <select name="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Choose category') }}</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>
                                    {{ $category->translations->firstWhere('locale', app()->getLocale())?->name ?? $category->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('service_category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Service code') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input name="code" value="{{ old('code') }}" class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror" required maxlength="60" pattern="[A-Za-z0-9_-]+" placeholder="e.g. ac-repair" style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('code')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-icons text-rounder-orange me-1"></i> {{ __('Icon') }}
                        </label>
                        <select name="icon" class="form-select @error('icon') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="">{{ __('Default / Auto') }}</option>
                            <option value="ac" @selected(old('icon') == 'ac')>❄️ {{ __('Air conditioner (ac)') }}</option>
                            <option value="drop" @selected(old('icon') == 'drop')>💧 {{ __('Plumbing (drop)') }}</option>
                            <option value="bolt" @selected(old('icon') == 'bolt')>⚡ {{ __('Electricity (bolt)') }}</option>
                            <option value="spark" @selected(old('icon') == 'spark')>✨ {{ __('Cleaning (spark)') }}</option>
                            <option value="washer" @selected(old('icon') == 'washer')>🧺 {{ __('Appliances (washer)') }}</option>
                            <option value="paint" @selected(old('icon') == 'paint')>🎨 {{ __('Paint (paint)') }}</option>
                            <option value="tool" @selected(old('icon') == 'tool')>🔧 {{ __('Tools & Furniture (tool)') }}</option>
                            <option value="home" @selected(old('icon') == 'home')>🏠 {{ __('General home (home)') }}</option>
                        </select>
                        @error('icon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Customer price before discount') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </span>
                            <input name="base_price" value="{{ old('base_price') }}" class="form-control border-start-0 font-monospace @error('base_price') is-invalid @enderror" type="number" step=".01" min="0" required placeholder="0.00" style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('base_price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-toggle-on text-rounder-orange me-1"></i> {{ __('Status') }}
                        </label>
                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="1" @selected(old('is_active', 1) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('is_active', 1) == 0)>{{ __('Inactive') }}</option>
                        </select>
                        @error('is_active')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pricing and Discount Fields --}}
                    @include('admin.packages.partials.pricing', ['item' => null, 'priceField' => 'base_price'])
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
                            'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'placeholder' => 'مثال: صيانة مكيفات سبليت', 'dir' => 'rtl'],
                            'en' => ['label' => 'English', 'flag' => '🇬🇧', 'placeholder' => 'e.g. Split AC Maintenance', 'dir' => 'ltr'],
                            'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'placeholder' => 'مثال: اسپلٹ اے سی سروس', 'dir' => 'rtl'],
                        ];
                    @endphp

                    @foreach($serviceLangs as $locale => $meta)
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-secondary">
                                <span class="me-1">{{ $meta['flag'] }}</span> {{ __('Service name') }} · {{ $meta['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input name="name_{{ $locale }}"
                                   value="{{ old('name_'.$locale) }}"
                                   class="form-control mb-2 @error('name_'.$locale) is-invalid @enderror"
                                   required
                                   maxlength="160"
                                   placeholder="{{ $meta['placeholder'] }}"
                                   dir="{{ $meta['dir'] }}"
                                   style="border-radius: 10px;">
                            @error('name_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label small fw-semibold text-secondary mt-1">
                                {{ __('Description') }} · {{ $meta['label'] }}
                            </label>
                            <textarea name="description_{{ $locale }}"
                                      rows="3"
                                      maxlength="5000"
                                      class="form-control @error('description_'.$locale) is-invalid @enderror"
                                      placeholder="{{ __('Description') }} ({{ $meta['label'] }})"
                                      dir="{{ $meta['dir'] }}"
                                      style="border-radius: 10px;">{{ old('description_'.$locale) }}</textarea>
                            @error('description_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
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
