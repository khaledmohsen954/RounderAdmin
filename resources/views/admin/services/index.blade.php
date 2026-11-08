@extends('layouts.admin')
@section('title', __('Available services') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-screwdriver-wrench text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Available services') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage platform main services, pricing details, and categories.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-screwdriver-wrench me-1 text-primary"></i>
                {{ __('Total services') }}: <strong class="text-dark">{{ $services->count() }}</strong>
            </span>
            @adminCan('admin.services.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.services.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create service') }}</span>
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
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-screwdriver-wrench me-1"></i> {{ __('Available services') }}
            </a>
        </li>
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 720px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 50px;" class="text-center">#</th>
                        <th>{{ __('Service') }}</th>
                        <th style="width: 180px;">{{ __('Category') }}</th>
                        <th style="width: 180px;">{{ __('Base price') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th style="width: 160px;" class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td class="text-center text-secondary small font-monospace">#{{ $service->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($service->image_path)
                                        <img class="rounded-3 border flex-shrink-0" width="46" height="46" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($service->image_path) }}" alt="{{ __('Product image') }}">
                                    @else
                                        @php
                                            $iconInfo = match($service->icon) {
                                                'ac' => ['fa-snowflake', '#0284c7', '#e0f2fe', '❄️ تكييف'],
                                                'drop' => ['fa-droplet', '#0284c7', '#e0f2fe', '💧 سباكة'],
                                                'bolt' => ['fa-bolt', '#d97706', '#fef3c7', '⚡ كهرباء'],
                                                'spark' => ['fa-wand-magic-sparkles', '#059669', '#d1fae5', '✨ نظافة'],
                                                'washer' => ['fa-soap', '#4f46e5', '#e0e7ff', '🧺 أجهزة'],
                                                'paint' => ['fa-paint-roller', '#db2777', '#fce7f3', '🎨 دهانات'],
                                                'tool' => ['fa-screwdriver-wrench', '#ea580c', '#ffedd5', '🔧 أدوات'],
                                                default => ['fa-house', '#0d9488', '#ccfbf1', '🏠 عام']
                                            };
                                        @endphp
                                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                             style="width: 46px; height: 46px; background-color: {{ $iconInfo[2] }}; color: {{ $iconInfo[1] }};"
                                             title="{{ $iconInfo[3] }}">
                                            <i class="fa-solid {{ $iconInfo[0] }}" style="font-size: 1.15rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $service->name ?? $service->code }}</div>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <code class="text-secondary small font-monospace">{{ $service->code }}</code>
                                            @if($service->icon)
                                                <span class="badge bg-light text-secondary border px-2 py-0.5" style="font-size: 0.72rem;">
                                                    {{ $iconInfo[3] ?? $service->icon }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-layer-group me-1"></i>
                                    {{ $service->category_name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    @if($service->price_before_discount > $service->base_price)
                                        <del class="small text-muted d-block font-monospace">{{ number_format($service->price_before_discount, 2) }}</del>
                                    @endif
                                    <span class="fw-bold text-dark font-monospace fs-6">{{ number_format($service->base_price, 2) }}</span>
                                    <small class="text-muted">SAR</small>
                                </div>
                            </td>
                            <td>
                                @if($service->is_active)
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
                                    @adminCan('admin.packages.services.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.packages.services.edit', $service->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.packages.services.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.packages.services.destroy', $service->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-screwdriver-wrench fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No services found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>
@endsection
