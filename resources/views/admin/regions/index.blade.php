@extends('layouts.admin')
@section('title', __('Regions') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-map-location-dot text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Regions') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage geographical regions and linked cities.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-map-location-dot me-1 text-primary"></i>
                {{ __('Total regions') }}: <strong class="text-dark">{{ $regions->total() }}</strong>
            </span>
            @adminCan('admin.regions.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.regions.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create region') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Regions & Cities --}}
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-map-location-dot me-1"></i> {{ __('Regions') }}
            </a>
        </li>
        @adminCan('admin.cities.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-tree-city me-1"></i> {{ __('Cities') }}
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
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control border-start-0" placeholder="{{ __('Search by name') }}..." style="border-radius: 10px 0 0 10px;">
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
                <a href="{{ route('admin.regions.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;">
                    {{ __('Clear') }}
                </a>
            </div>
            @endif
        </form>

        {{-- Regions Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 620px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 70px;">#</th>
                        <th>{{ __('Region name') }}</th>
                        <th style="width: 220px;">{{ __('Linked cities count') }}</th>
                        <th style="width: 140px;">{{ __('Status') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $region)
                        @php
                            $name = $region->translations->firstWhere('locale', $locale)?->name
                                ?? $region->translations->firstWhere('locale', 'ar')?->name
                                ?? $region->translations->first()?->name
                                ?? $region->code;
                            $enName = $region->translations->firstWhere('locale', 'en')?->name;
                        @endphp
                        <tr>
                            <td class="fw-semibold text-secondary">#{{ $region->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-map-location-dot" style="font-size: 0.95rem;"></i>
                                    </span>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $name }}</div>
                                        @if($enName && $enName !== $name)
                                            <div class="small text-muted">{{ $enName }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                    <i class="fa-solid fa-city me-1 text-primary"></i>
                                    {{ number_format($region->cities_count) }} {{ __('Cities') }}
                                </span>
                            </td>
                            <td>
                                @if($region->is_active)
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
                                    @adminCan('admin.regions.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.regions.edit', $region->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.regions.destroy', 'DELETE')
                                    @if($region->cities_count > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary disabled" title="{{ __('Cannot delete region with linked cities') }}" style="border-radius: 8px; opacity: 0.5;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @else
                                        @include('admin.partials.delete-button', ['action' => route('admin.regions.destroy', $region->id)])
                                    @endif
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-map-location-dot fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No regions found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($regions->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $regions->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
