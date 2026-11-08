@extends('layouts.admin')
@section('title', __('Edit sub service') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.sub-services.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Sub services') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit sub service') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-rounder-orange"></i>
                <span>{{ __('Edit sub service') }}: {{ $subService->translations->firstWhere('locale', app()->getLocale())?->name ?? $subService->code }}</span>
                <span class="badge bg-light text-dark border px-2 py-1 fs-6 font-monospace">{{ $subService->code }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update sub-service details, translations, main service link, and image.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.sub-services.update', 'PUT')
            @include('admin.sub-services.partials.form', [
                'action' => route('admin.sub-services.update', $subService->id),
                'method' => 'PUT',
                'subService' => $subService,
                'names' => $names ?? collect(),
                'descriptions' => $descriptions ?? collect(),
            ])
        @endadminCan
    </div>
</main>
@endsection
