@extends('layouts.admin')
@section('title', __('Edit service category') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.service-categories.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Service categories') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit service category') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-layer-group text-rounder-orange"></i>
                <span>{{ __('Edit service category') }}: {{ $category->translations->firstWhere('locale', app()->getLocale())?->name ?? $category->code }}</span>
                <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">{{ $category->code }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update service category details, translations, and display image.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.service-categories.update', 'PUT')
            @include('admin.service-categories.partials.form', [
                'action' => route('admin.service-categories.update', $category->id),
                'method' => 'PUT',
                'category' => $category,
                'names' => $names ?? collect(),
            ])
        @endadminCan
    </div>
</main>
@endsection
