@extends('layouts.admin')
@section('title', __('Sub services') . ' · Rounder')
@section('body')
@php $locale = $locale ?? app()->getLocale(); @endphp
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Sub services') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage granular sub-services and tasks associated with main services.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-list-check me-1 text-primary"></i>
                {{ __('Total sub services') }}: <strong class="text-dark">{{ $subServices->total() }}</strong>
            </span>
            @adminCan('admin.sub-services.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.sub-services.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create sub service') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs --}}
    <ul class="nav nav-pills mb-4 gap-2">
        @adminCan('admin.service-categories.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.service-categories.*') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-layer-group me-1"></i> {{ __('Service categories') }}
            </a>
        </li>
        @endadminCan
        @adminCan('admin.services.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
            </a>
        </li>
        @endadminCan
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.sub-services.*') ? 'active' : '' }}" href="{{ route('admin.sub-services.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-list-check me-1"></i> {{ __('Sub services') }}
            </a>
        </li>
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
                <select name="service_id" class="form-select" style="border-radius: 10px;">
                    <option value="">{{ __('All services') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(($filters['service_id'] ?? null) == $service->id)>
                            {{ $service->translations->firstWhere('locale', $locale)?->name ?? $service->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input name="search" value="{{ $filters['search'] ?? '' }}" class="form-control border-start-0" placeholder="{{ __('Search') }}..." style="border-radius: 10px 0 0 10px;">
                </div>
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-outline-primary w-100 d-inline-flex align-items-center justify-content-center gap-1" style="border-radius: 10px;">
                    <i class="fa-solid fa-filter"></i>
                    <span>{{ __('Filter') }}</span>
                </button>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 680px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Sub service') }}</th>
                        <th style="width: 200px;">{{ __('Service') }}</th>
                        <th style="width: 140px;">{{ __('Sort order') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th style="width: 160px;" class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subServices as $sub)
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $sub->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($sub->image_path)
                                        <img class="rounded-3 border flex-shrink-0" width="46" height="46" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($sub->image_path) }}" alt="{{ __('Product image') }}">
                                    @else
                                        <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background-color: #fff3eb; color: var(--orange, #fb822f);">
                                            <i class="fa-solid fa-list-check" style="font-size: 1.1rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $sub->translations->firstWhere('locale', $locale)?->name ?? $sub->code }}</div>
                                        <code class="text-secondary small font-monospace">{{ $sub->code }}</code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-screwdriver-wrench me-1"></i>
                                    {{ $sub->service?->translations->firstWhere('locale', $locale)?->name ?? $sub->service?->code }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border font-monospace px-2 py-1">
                                    <i class="fa-solid fa-arrow-down-1-9 me-1 text-muted"></i> {{ $sub->sort_order }}
                                </span>
                            </td>
                            <td>
                                @if($sub->is_active)
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
                                    @adminCan('admin.sub-services.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.sub-services.edit', $sub->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.sub-services.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.sub-services.destroy', $sub->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-list-check fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No sub services found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subServices->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $subServices->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
