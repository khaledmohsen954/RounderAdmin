@extends('layouts.admin')
@section('title', ($initiative ? __('Edit initiative') : __('Edit announcement')) . ' · Rounder')
@section('body')
<style>
.bilingual-nav-pills {
    background: #f1f5f3;
    padding: 4px;
    border-radius: 10px;
    display: inline-flex;
}
.bilingual-nav-pills .nav-link {
    border-radius: 8px;
    color: #55726a;
    font-size: 0.85rem;
    font-weight: 600;
    padding: 6px 16px;
    border: none;
}
.bilingual-nav-pills .nav-link.active {
    background: #ffffff !important;
    color: #0e3c36 !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}
</style>

<main class="container py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <a href="{{ route('admin.content.index', ['tab' => $initiative ? 'initiatives' : 'announcements']) }}" class="text-decoration-none text-secondary">
                    <i class="fa-solid fa-arrow-right me-1"></i>
                    {{ __('Resident app management') }}
                </a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $initiative ? __('Community initiatives') : __('Community announcements') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-0 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                    <i class="fa-solid {{ $initiative ? 'fa-lightbulb' : 'fa-bullhorn' }}" style="font-size: 1rem;"></i>
                </span>
                <span>{{ $initiative ? __('Edit initiative') : __('Edit announcement') }}</span>
                <span class="badge bg-light text-secondary border font-monospace fs-6">#{{ $record->id }}</span>
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.content.index', ['tab' => $initiative ? 'initiatives' : 'announcements']) }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Edit Card --}}
    <form class="panel p-4" method="post" action="{{ route($initiative ? 'admin.content.initiatives.update' : 'admin.content.announcements.update', $record->id) }}" style="border-top: 4px solid var(--orange, #fb822f) !important;">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Target Home & Publish --}}
            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-secondary">
                    <i class="fa-solid fa-building me-1 text-rounder-orange"></i>
                    {{ __('Home') }} @if($initiative)<span class="text-danger">*</span>@endif
                </label>
                <select name="home_id" class="form-select" @required($initiative) style="border-radius: 10px;">
                    @if(!$initiative)
                        <option value="">{{ __('All homes') }}</option>
                    @endif
                    @foreach($homes as $home)
                        <option value="{{ $home->id }}" @selected(old('home_id', $record->home_id) == $home->id)>
                            {{ $home->name }} · #{{ $home->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label fw-semibold small text-secondary">
                    <i class="fa-solid fa-satellite-dish me-1 text-rounder-orange"></i>
                    {{ __('Publish') }} <span class="text-danger">*</span>
                </label>
                <select class="form-select" name="{{ $initiative ? 'is_published' : 'published' }}" style="border-radius: 10px;">
                    <option value="1" @selected(old($initiative ? 'is_published' : 'published', $record->is_published) == 1)>{{ __('Yes') }} ({{ __('Published') }})</option>
                    <option value="0" @selected(old($initiative ? 'is_published' : 'published', $record->is_published) == 0)>{{ __('No') }} ({{ __('Draft') }})</option>
                </select>
            </div>

            @if($initiative)
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-calendar-plus me-1 text-rounder-orange"></i>
                        {{ __('Starts at') }} <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="form-control" name="starts_at" value="{{ old('starts_at', $record->starts_at?->format('Y-m-d\TH:i')) }}" required style="border-radius: 10px;">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-calendar-check me-1 text-rounder-orange"></i>
                        {{ __('Ends at') }} <span class="text-danger">*</span>
                    </label>
                    <input type="datetime-local" class="form-control" name="ends_at" value="{{ old('ends_at', $record->ends_at?->format('Y-m-d\TH:i')) }}" required style="border-radius: 10px;">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-coins me-1 text-rounder-orange"></i>
                        {{ __('Target amount') }} ({{ __('SAR') }})
                    </label>
                    <div class="input-group">
                        <input type="number" step=".01" min="0" name="target_amount" class="form-control" value="{{ old('target_amount', $record->target_amount) }}" style="border-radius: 0 10px 10px 0;">
                        <span class="input-group-text bg-light text-secondary small fw-bold" style="border-radius: 10px 0 0 10px;">ر.س</span>
                    </div>
                </div>
            @else
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-calendar-xmark me-1 text-rounder-orange"></i>
                        {{ __('Expires at') }}
                    </label>
                    <input type="datetime-local" class="form-control" name="expires_at" value="{{ old('expires_at', $record->expires_at?->format('Y-m-d\TH:i')) }}" style="border-radius: 10px;">
                </div>
            @endif

            {{-- Bilingual Fields Card --}}
            <div class="col-12">
                <div class="bg-white p-3 p-md-4 rounded-3 border" style="border-color: #e5ece9 !important;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small fw-semibold text-secondary">
                            <i class="fa-solid fa-language me-1 text-rounder-orange"></i>
                            {{ __('Title') }} & {{ $initiative ? __('Description') : __('Body') }}
                        </span>
                        <ul class="nav nav-pills bilingual-nav-pills" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="edit-ar-tab" data-bs-toggle="pill" data-bs-target="#edit-ar" type="button" role="tab">
                                    🇸🇦 العربية
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="edit-en-tab" data-bs-toggle="pill" data-bs-target="#edit-en" type="button" role="tab">
                                    🇬🇧 English
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content">
                        @foreach(['ar' => 'العربية', 'en' => 'English'] as $locale => $label)
                            @php($translation = $record->translations ? $record->translations->firstWhere('locale', $locale) : null)
                            <div class="tab-pane fade {{ $locale === 'ar' ? 'show active' : '' }}" id="edit-{{ $locale }}" role="tabpanel">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-secondary">
                                        {{ __('Title') }} · {{ $label }} <span class="text-danger">*</span>
                                    </label>
                                    <input class="form-control" name="title_{{ $locale }}" value="{{ old('title_'.$locale, $translation?->title) }}" required minlength="3" maxlength="180" style="border-radius: 10px;">
                                </div>
                                <div>
                                    <label class="form-label fw-semibold small text-secondary">
                                        {{ $initiative ? __('Description') : __('Body') }} · {{ $label }} <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" rows="5" name="{{ ($initiative ? 'description_' : 'body_').$locale }}" required minlength="5" maxlength="{{ $initiative ? 5000 : 20000 }}" style="border-radius: 10px;">{{ old(($initiative ? 'description_' : 'body_').$locale, $initiative ? $translation?->description : $translation?->body) }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Form Footer --}}
            <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                <a href="{{ route('admin.content.index', ['tab' => $initiative ? 'initiatives' : 'announcements']) }}" class="btn btn-light px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4" style="border-radius: 10px;">
                    <i class="fa-solid fa-check me-1"></i>
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </form>
</main>
@endsection
