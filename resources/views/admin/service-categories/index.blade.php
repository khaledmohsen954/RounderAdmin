@extends('layouts.admin')
@section('title', __('Service categories') . ' · Rounder')
@section('body')
@php $locale = $locale ?? app()->getLocale(); @endphp
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-layer-group text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Service categories') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage high-level service classifications and departments.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-layer-group me-1 text-primary"></i>
                {{ __('Total service categories') }}: <strong class="text-dark">{{ $categories->total() }}</strong>
            </span>
            @adminCan('admin.service-categories.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.service-categories.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create service category') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Categories, Services, Sub-services, Prices --}}
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-layer-group me-1"></i> {{ __('Service categories') }}
            </a>
        </li>
        @adminCan('admin.services.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
            </a>
        </li>
        @endadminCan
        @adminCan('admin.sub-services.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.sub-services.*') ? 'active' : '' }}" href="{{ route('admin.sub-services.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
            </a>
        </li>
        @endadminCan
        @adminCan('admin.prices.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.prices.*') ? 'active' : '' }}" href="{{ route('admin.prices.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-tags me-1"></i> {{ __('Prices') }}
            </a>
        </li>
        @endadminCan
    </ul>

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        {{-- Search & Filters --}}
        <form method="get" class="row g-2 mb-4 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control border-start-0" placeholder="{{ __('Search by name or code') }}..." style="border-radius: 10px 0 0 10px;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="status" class="form-select" style="border-radius: 10px;">
                    <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>{{ __('All statuses') }}</option>
                    <option value="active" @selected(($filters['status'] ?? null) === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(($filters['status'] ?? null) === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <button class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 10px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>{{ __('Filter') }}</span>
                </button>
            </div>
            @if(!empty($filters['search']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
            <div class="col-12 col-md-2">
                <a href="{{ route('admin.service-categories.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                    {{ __('Clear') }}
                </a>
            </div>
            @endif
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 620px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th style="width: 70px;">{{ __('Image') }}</th>
                        <th>{{ __('Category name') }}</th>
                        <th style="width: 160px;">{{ __('Code') }}</th>
                        <th style="width: 160px;">{{ __('Services count') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $category->id }}</td>
                            <td>
                                @if($category->image_path)
                                    <img class="rounded-3 border" width="46" height="46" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt="{{ __('Category image') }}">
                                @else
                                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px; background-color: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-layer-group" style="font-size: 1.1rem;"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $category->translations->firstWhere('locale', $locale)?->name ?? $category->code }}</div>
                                <div class="small text-secondary mt-1">
                                    {{ $category->translations->pluck('name')->filter()->join(' · ') }}
                                </div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded text-dark border font-monospace" style="font-size: 0.85rem;">{{ $category->code }}</code>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-wrench me-1"></i>
                                    {{ $category->services_count }} {{ __('Services') }}
                                </span>
                            </td>
                            <td>
                                @if($category->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-check me-1"></i> {{ __('Active') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-circle-xmark me-1"></i> {{ __('Inactive') }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @adminCan('admin.service-categories.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.service-categories.edit', $category->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.service-categories.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.service-categories.destroy', $category->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-folder-open fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No service categories found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
