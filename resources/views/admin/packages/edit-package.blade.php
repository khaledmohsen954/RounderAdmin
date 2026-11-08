@extends('layouts.admin')
@section('title', __('Edit package') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.packages.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Customer packages') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit package') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-rounder-orange"></i>
                <span>{{ __('Edit package') }}: {{ $translations->get(app()->getLocale())?->name ?? $package->code }}</span>
                <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">{{ $package->code }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update subscription package details, pricing, features, and included services.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.packages.update', 'PUT')
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.update', $package->id) }}">
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
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Package code') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input name="code" value="{{ old('code', $package->code) }}" class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror" required maxlength="60" style="border-radius: 10px 0 0 10px;">
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-calendar-check text-rounder-orange me-1"></i> {{ __('Billing cycle') }} <span class="text-danger">*</span>
                        </label>
                        <select name="billing_cycle_id" class="form-select @error('billing_cycle_id') is-invalid @enderror" required style="border-radius: 10px;">
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected(old('billing_cycle_id', $package->billing_cycle_id) == $cycle->id)>
                                    {{ __($cycle->code === 'semiannual' ? 'Semiannual' : ucfirst($cycle->code)) }} · {{ $cycle->months }} {{ __('months') }}
                                </option>
                            @endforeach
                        </select>
                        @error('billing_cycle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Customer price before discount') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </span>
                            <input name="price" value="{{ old('price', $package->price_before_discount ?? $package->price) }}" class="form-control border-start-0 font-monospace @error('price') is-invalid @enderror" type="number" min="0" max="9999999999.99" step=".01" required style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Currency') }} <span class="text-danger">*</span>
                        </label>
                        <input name="currency_code" value="{{ old('currency_code', $package->currency_code) }}" class="form-control font-monospace text-center fw-bold @error('currency_code') is-invalid @enderror" pattern="[A-Z]{3}" maxlength="3" required style="border-radius: 10px;">
                        @error('currency_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-1">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Duration') }}
                        </label>
                        <input name="duration_months" value="{{ old('duration_months', $package->duration_months) }}" class="form-control font-monospace text-center @error('duration_months') is-invalid @enderror" type="number" min="1" max="120" required style="border-radius: 10px;">
                    </div>

                    <div class="col-md-1">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Status') }}
                        </label>
                        <select name="is_active" class="form-select @error('is_active') is-invalid @enderror" style="border-radius: 10px;">
                            <option value="1" @selected(old('is_active', $package->is_active) == 1)>{{ __('Active') }}</option>
                            <option value="0" @selected(old('is_active', $package->is_active) == 0)>{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    {{-- Pricing and Discount Fields --}}
                    @include('admin.packages.partials.pricing', ['item' => $package, 'priceField' => 'price'])
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
                        $packageLangs = [
                            'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl'],
                            'en' => ['label' => 'English', 'flag' => '🇬🇧', 'dir' => 'ltr'],
                            'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'dir' => 'rtl'],
                        ];
                    @endphp

                    @foreach($packageLangs as $locale => $meta)
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-secondary">
                                <span class="me-1">{{ $meta['flag'] }}</span> {{ __('Package name') }} · {{ $meta['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input name="name_{{ $locale }}"
                                   value="{{ old('name_'.$locale, $translations->get($locale)?->name) }}"
                                   class="form-control mb-2 @error('name_'.$locale) is-invalid @enderror"
                                   required
                                   maxlength="140"
                                   dir="{{ $meta['dir'] }}"
                                   style="border-radius: 10px;">
                            @error('name_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label small fw-semibold text-secondary mt-1">
                                {{ __('Package details') }} · {{ $meta['label'] }}
                            </label>
                            <textarea name="description_{{ $locale }}"
                                      rows="3"
                                      maxlength="5000"
                                      class="form-control @error('description_'.$locale) is-invalid @enderror"
                                      dir="{{ $meta['dir'] }}"
                                      style="border-radius: 10px;">{{ old('description_'.$locale, $translations->get($locale)?->description) }}</textarea>
                            @error('description_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. Comparison Values --}}
            @include('admin.packages.partials.comparison-values')

            {{-- 4. Included Services --}}
            @include('admin.packages.partials.included-services')

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save package') }}</span>
                </button>
            </div>
        </form>
        @endadminCan
    </div>
</main>
@endsection
