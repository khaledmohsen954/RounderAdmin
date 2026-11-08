@extends('layouts.admin')
@section('title', __('Edit region') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.regions.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Regions') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit region') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-rounder-orange"></i>
                <span>{{ __('Edit region') }}: {{ $region->translations->firstWhere('locale', app()->getLocale())?->name ?? $region->code }}</span>
                <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">{{ $region->code }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update region details and multilingual translations.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.regions.update', 'PUT')
            @include('admin.regions.partials.form', [
                'action' => route('admin.regions.update', $region->id),
                'method' => 'PUT',
                'region' => $region,
                'names' => $names ?? collect(),
            ])
        @endadminCan
    </div>
</main>
@endsection
