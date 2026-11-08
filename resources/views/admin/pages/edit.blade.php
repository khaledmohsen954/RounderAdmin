@extends('layouts.admin')
@section('title', __('Edit page') . ' · Rounder')
@section('body')
<style>
.bilingual-nav-pills {
    background: #f1f5f3;
    padding: 3px;
    border-radius: 10px;
    display: inline-flex;
    border: 1px solid #e2ece7;
}
.bilingual-nav-pills .nav-link {
    border-radius: 8px;
    color: #4b635d;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 6px 16px;
    border: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
}
.bilingual-nav-pills .nav-link:hover {
    color: #0e3c36;
}
.bilingual-nav-pills .nav-link.active {
    background: #0e3c36 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(14, 60, 54, 0.15);
}
.page-content-editor {
    border-radius: 10px;
    border: 1px solid #dce6e3;
    font-family: inherit;
    font-size: 0.95rem;
    line-height: 1.8;
    padding: 14px 16px;
    background: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.page-content-editor:focus {
    border-color: var(--orange, #fb822f);
    box-shadow: 0 0 0 3px rgba(251, 130, 47, 0.12);
}
</style>

<main class="container py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <a href="{{ route('admin.pages.index') }}" class="text-decoration-none text-secondary">
                    <i class="fa-solid fa-arrow-right me-1"></i>
                    {{ __('Pages and policies') }}
                </a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $translationAr?->title ?? $page->slug }}</span>
            </div>
            <h1 class="h3 fw-bold mb-0 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                    <i class="fa-solid fa-file-contract" style="font-size: 0.95rem;"></i>
                </span>
                <span>{{ __('Edit page') }}:</span>
                <span class="text-dark">{{ $translationAr?->title ?? $page->slug }}</span>
                <span class="badge bg-light text-secondary border font-monospace fs-6">
                    {{ $page->slug }}
                </span>
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Edit Form --}}
    <form method="post" action="{{ route('admin.pages.update', $page->id) }}">
        @csrf
        @method('put')

        <div class="panel p-3 p-md-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f) !important; border-radius: 14px;">
            {{-- Status Bar & Bilingual Switcher --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 mb-3 border-bottom">
                <div>
                    <ul class="nav nav-pills bilingual-nav-pills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-ar-btn" data-bs-toggle="pill" data-bs-target="#tab-ar" type="button" role="tab">
                                <span class="me-1">🇸🇦</span>
                                <span>{{ __('Arabic Content') }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-en-btn" data-bs-toggle="pill" data-bs-target="#tab-en" type="button" role="tab">
                                <span class="me-1">🇬🇧</span>
                                <span>{{ __('English Content') }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label for="is_active" class="form-label fw-semibold small text-secondary mb-0">
                        {{ __('Status') }}:
                    </label>
                    <select name="is_active" id="is_active" class="form-select form-select-sm" style="width: 130px; border-radius: 8px;">
                        <option value="1" @selected(old('is_active', $page->is_active) == 1)>{{ __('Active') }}</option>
                        <option value="0" @selected(old('is_active', $page->is_active) == 0)>{{ __('Inactive') }}</option>
                    </select>
                </div>
            </div>

            {{-- Tab Contents --}}
            <div class="tab-content">
                {{-- TAB: ARABIC --}}
                <div class="tab-pane fade show active" id="tab-ar" role="tabpanel">
                    <div class="mb-3">
                        <label for="title_ar" class="form-label fw-semibold small text-dark mb-1">
                            {{ __('Page title in Arabic') }} <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('title_ar') is-invalid @enderror" 
                               id="title_ar" 
                               name="title_ar" 
                               value="{{ old('title_ar', $translationAr?->title) }}" 
                               dir="rtl" 
                               required 
                               style="border-radius: 10px; font-weight: 600;">
                        @error('title_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="content_ar" class="form-label fw-semibold small text-dark mb-1">
                            {{ __('Page content in Arabic') }} <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control page-content-editor @error('content_ar') is-invalid @enderror" 
                                  id="content_ar" 
                                  name="content_ar" 
                                  rows="14" 
                                  dir="rtl" 
                                  required 
                                  placeholder="اكتب نصوص وبنود الصفحة هنا...">{{ old('content_ar', $translationAr?->content) }}</textarea>
                        @error('content_ar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- TAB: ENGLISH --}}
                <div class="tab-pane fade" id="tab-en" role="tabpanel">
                    <div class="mb-3">
                        <label for="title_en" class="form-label fw-semibold small text-dark mb-1">
                            {{ __('Page title in English') }}
                        </label>
                        <input type="text" 
                               class="form-control @error('title_en') is-invalid @enderror" 
                               id="title_en" 
                               name="title_en" 
                               value="{{ old('title_en', $translationEn?->title) }}" 
                               dir="ltr" 
                               style="border-radius: 10px; font-weight: 600; text-align: left;">
                        @error('title_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-2">
                        <label for="content_en" class="form-label fw-semibold small text-dark mb-1">
                            {{ __('Page content in English') }}
                        </label>
                        <textarea class="form-control page-content-editor @error('content_en') is-invalid @enderror" 
                                  id="content_en" 
                                  name="content_en" 
                                  rows="14" 
                                  dir="ltr" 
                                  style="text-align: left;"
                                  placeholder="Enter page terms and policy content in English...">{{ old('content_en', $translationEn?->content) }}</textarea>
                        @error('content_en')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Card Footer --}}
            <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                <a href="{{ route('admin.pages.index') }}" class="btn btn-light px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4" style="border-radius: 10px;">
                    <i class="fa-solid fa-check me-1"></i>
                    {{ __('Save changes') }}
                </button>
            </div>
        </div>
    </form>
</main>
@endsection
