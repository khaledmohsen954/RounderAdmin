@extends('layouts.admin')
@section('title', __('Customer subscriptions') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-repeat text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Customer subscriptions') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('View and track resident subscriptions to customer packages.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-repeat me-1 text-primary"></i>
                {{ __('Total subscriptions') }}: <strong class="text-dark">{{ $subscriptions->total() }}</strong>
            </span>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Packages, Features & Subscriptions --}}
    <ul class="nav nav-pills mb-4 gap-2">
        @adminCan('admin.packages.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> {{ __('Customer packages') }}
            </a>
        </li>
        @endadminCan
        @adminCan('admin.package-features.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.package-features.*') ? 'active' : '' }}" href="{{ route('admin.package-features.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-sliders me-1"></i> {{ __('Package comparison features') }}
            </a>
        </li>
        @endadminCan
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-repeat me-1"></i> {{ __('Customer subscriptions') }}
            </a>
        </li>
    </ul>

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 780px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Resident') }}</th>
                        <th>{{ __('Home') }}</th>
                        <th>{{ __('Package') }}</th>
                        <th style="width: 140px;">{{ __('Price') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th style="width: 140px;">{{ __('Start date') }}</th>
                        <th style="width: 140px;">{{ __('End date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $subscription)
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $subscription->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0"
                                         style="width: 36px; height: 36px; background-color: #f0f4f8; color: var(--bs-primary);">
                                        <i class="fa-solid fa-user" style="font-size: 0.85rem;"></i>
                                    </div>
                                    <span class="fw-bold text-dark">{{ $subscription->resident_name }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-1 text-secondary fw-semibold">
                                    <i class="fa-solid fa-building text-muted"></i>
                                    {{ $subscription->home_name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 fw-semibold">
                                    <i class="fa-solid fa-box-open text-rounder-orange me-1"></i>
                                    {{ $subscription->package_name ?? $subscription->reference }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-dark font-monospace fs-6">
                                    {{ number_format($subscription->price_snapshot, 2) }}
                                </span>
                                <small class="text-muted">{{ $subscription->currency_code }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                    {{ $subscription->status_name }}
                                </span>
                            </td>
                            <td>
                                <div class="small text-secondary font-monospace">
                                    <i class="fa-regular fa-calendar me-1 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($subscription->starts_at)->format('Y-m-d') }}
                                </div>
                            </td>
                            <td>
                                <div class="small text-secondary font-monospace">
                                    <i class="fa-regular fa-calendar-check me-1 text-muted"></i>
                                    {{ \Carbon\Carbon::parse($subscription->ends_at)->format('Y-m-d') }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-repeat fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No subscriptions found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
