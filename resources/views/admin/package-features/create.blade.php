@extends('layouts.admin')
@section('title', __('Add comparison feature') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.package-features.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Package comparison features') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Add comparison feature') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-sliders text-rounder-orange"></i>
                <span>{{ __('Add comparison feature') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Define a new feature specification to compare between customer packages.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.package-features.store', 'POST')
        <form method="post" action="{{ route('admin.package-features.store') }}">
            @csrf

            {{-- 1. Basic Settings --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                    <span>{{ __('Basic settings') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Feature code') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input name="code" value="{{ old('code') }}" class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror" required maxlength="60" pattern="[A-Za-z0-9_-]+" placeholder="{{ __('e.g. 24_7_support, free_maintenance') }}" style="border-radius: 10px 0 0 10px;">
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-arrow-down-1-9 text-rounder-orange me-1"></i> {{ __('Display order') }} <span class="text-danger">*</span>
                        </label>
                        <input name="sort_order" type="number" min="0" max="65535" value="{{ old('sort_order', 0) }}" class="form-control font-monospace @error('sort_order') is-invalid @enderror" required style="border-radius: 10px;">
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3">
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
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Multilingual Names --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-language text-rounder-orange"></i>
                    <span>{{ __('Multilingual names') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary">
                            🇸🇦 {{ __('Feature name') }} (العربية) <span class="text-danger">*</span>
                        </label>
                        <input name="name_ar" value="{{ old('name_ar') }}" class="form-control @error('name_ar') is-invalid @enderror" required maxlength="160" dir="rtl" placeholder="مثال: خدمة طوارئ 24/7" style="border-radius: 10px;">
                        @error('name_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary">
                            🇬🇧 {{ __('Feature name') }} (English) <span class="text-danger">*</span>
                        </label>
                        <input name="name_en" value="{{ old('name_en') }}" class="form-control @error('name_en') is-invalid @enderror" required maxlength="160" dir="ltr" placeholder="e.g. 24/7 Emergency Support" style="border-radius: 10px;">
                        @error('name_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary">
                            🇵🇰 {{ __('Feature name') }} (اردو) <span class="text-danger">*</span>
                        </label>
                        <input name="name_ur" value="{{ old('name_ur') }}" class="form-control @error('name_ur') is-invalid @enderror" required maxlength="160" dir="rtl" placeholder="مثال: 24/7 ہنگامی سپورٹ" style="border-radius: 10px;">
                        @error('name_ur')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('admin.package-features.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save') }}</span>
                </button>
            </div>
        </form>
        @endadminCan
    </div>
</main>
@endsection
