@extends('layouts.admin')
@section('title', __('Cities') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-tree-city text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Cities') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage cities and link them to geographical regions.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-tree-city me-1 text-primary"></i>
                {{ __('Total cities') }}: <strong class="text-dark">{{ $cities->total() }}</strong>
            </span>
            @adminCan('admin.cities.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.cities.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create city') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Regions & Cities --}}
    <ul class="nav nav-pills mb-4 gap-2">
        @adminCan('admin.regions.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.regions.*') ? 'active' : '' }}" href="{{ route('admin.regions.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-map-location-dot me-1"></i> {{ __('Regions') }}
            </a>
        </li>
        @endadminCan
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.cities.*') ? 'active' : '' }}" href="{{ route('admin.cities.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-tree-city me-1"></i> {{ __('Cities') }}
            </a>
        </li>
    </ul>

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        {{-- Search & Filters --}}
        <form method="get" class="row g-2 mb-4 align-items-center">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control border-start-0" placeholder="{{ __('Search by name or code') }}..." style="border-radius: 10px 0 0 10px;">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select name="region_id" class="form-select" style="border-radius: 10px;">
                    <option value="">{{ __('All regions') }}</option>
                    @foreach($regions as $r)
                        @php
                            $rName = $r->translations->firstWhere('locale', $locale)?->name
                                ?? $r->translations->firstWhere('locale', 'ar')?->name
                                ?? $r->code;
                        @endphp
                        <option value="{{ $r->id }}" @selected(($filters['region_id'] ?? null) == $r->id)>
                            {{ $rName }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
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
            @if(!empty($filters['search']) || !empty($filters['region_id']) || (!empty($filters['status']) && $filters['status'] !== 'all'))
            <div class="col-6 col-md-1">
                <a href="{{ route('admin.cities.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 10px;" title="{{ __('Clear') }}">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
            @endif
        </form>

        {{-- Cities Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 760px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 70px;">#</th>
                        <th>{{ __('City name') }}</th>
                        <th style="width: 140px;">{{ __('City code') }}</th>
                        <th style="width: 180px;">{{ __('Region') }}</th>
                        <th style="width: 180px;">{{ __('Linked homes count') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cities as $city)
                        @php
                            $name = $city->translations->firstWhere('locale', $locale)?->name
                                ?? $city->translations->firstWhere('locale', 'ar')?->name
                                ?? $city->translations->first()?->name
                                ?? $city->code;
                            $enName = $city->translations->firstWhere('locale', 'en')?->name;
                            $regionName = $city->region?->translations->firstWhere('locale', $locale)?->name
                                ?? $city->region?->translations->firstWhere('locale', 'ar')?->name
                                ?? $city->region?->code;
                        @endphp
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $city->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                         style="width: 38px; height: 38px; background-color: #fff3eb; color: var(--orange, #fb822f);">
                                        <i class="fa-solid fa-tree-city" style="font-size: 0.95rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $name }}</span>
                                        @if($enName && $enName !== $name)
                                            <span class="text-secondary small" dir="ltr">{{ $enName }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded text-dark border font-monospace" style="font-size: 0.85rem;">{{ $city->code }}</code>
                            </td>
                            <td>
                                @if($city->region)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                        <i class="fa-solid fa-map-location-dot me-1"></i> {{ $regionName }}
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                @if($city->homes_count > 0)
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-city me-1"></i> {{ number_format($city->homes_count) }} {{ __('Homes') }}
                                    </span>
                                @else
                                    <span class="text-muted small">0 {{ __('Homes') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($city->is_active)
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
                                    @adminCan('admin.cities.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.cities.edit', $city->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.cities.destroy', 'DELETE')
                                    @if($city->homes_count > 0)
                                        <button type="button" class="btn btn-sm btn-outline-secondary disabled" title="{{ __('Cannot delete city with associated homes.') }}" style="border-radius: 8px; opacity: 0.5;">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    @else
                                        @include('admin.partials.delete-button', ['action' => route('admin.cities.destroy', $city->id)])
                                    @endif
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-tree-city fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No cities found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cities->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $cities->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
