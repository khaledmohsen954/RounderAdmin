@extends('layouts.provider')
@section('title', __('Dashboard'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="text-secondary">{{ __('Provider portal') }}</div>
        <h1 class="h3 fw-bold mb-0">{{ $profile->company_name }}</h1>
    </div>
    <div class="row g-3 mb-4">
        @foreach([
            ['orders.view', 'provider.orders.index', 'fa-calendar-check', __('Bookings')],
            ['emergencies.view', 'provider.emergencies.index', 'fa-triangle-exclamation', __('Emergency requests')],
            ['prices.view', 'provider.prices.index', 'fa-tags', __('Prices and approvals')],
            ['finance.view', 'provider.finance.index', 'fa-coins', __('Financial accounts')],
            ['ratings.view', 'provider.ratings.index', 'fa-star', __('Ratings')],
            ['users.manage', 'provider.team.index', 'fa-users-gear', __('Users and permissions')],
            ['profile.manage', 'provider.settings.index', 'fa-gear', __('Settings / Profile')],
            ['contract.view', 'provider.contract', 'fa-file-contract', __('Provider contract')],
        ] as [$permission, $route, $icon, $label])
            @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, $permission))
                <div class="col-6 col-lg-3">
                    <a class="panel p-3 h-100 d-flex align-items-center gap-3 text-decoration-none text-dark" href="{{ route($route) }}">
                        <i class="fa-solid {{ $icon }} text-rounder-orange fs-4"></i><span class="fw-semibold">{{ $label }}</span>
                    </a>
                </div>
            @endif
        @endforeach
    </div>
    @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, 'orders.create'))
        <a class="btn btn-orange" href="{{ route('provider.orders.create') }}">+ {{ __('Create service order') }}</a>
    @endif
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-2">
        <div class="metric">
            <div class="text-secondary">{{ __('Total orders') }}</div>
            <div class="value">{{ $ordersCount }}</div>
        </div>
    </div>
    @foreach(($visibleBookingStatuses ?? collect()) as $st)
        <div class="col-6 col-xl-2">
            <a href="{{ route('provider.orders.index', ['status' => $st->code]) }}" class="text-decoration-none text-dark d-block">
                <div class="metric">
                    <div class="text-secondary">{{ $st->translations->firstWhere('locale', app()->getLocale())?->name ?? $st->code }}</div>
                    <div class="value">{{ $st->provider_orders_count ?? 0 }}</div>
                </div>
            </a>
        </div>
    @endforeach
</div>
@if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, 'orders.view'))
<section class="panel p-3 p-lg-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 fw-bold mb-0">{{ __('Recent service orders') }}</h2>
        @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, 'orders.view'))
            <a href="{{ route('provider.orders.index') }}">{{ __('View all') }}</a>
        @endif
    </div>
    @include('provider.orders.table', ['orders' => $orders])
</section>
@endif
<div class="row g-3 mt-1">
    <div class="col-lg-5">
        <section class="panel p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0">{{ __('Your registered services') }}</h2>
                <span class="badge text-bg-light">{{ $services->count() }}</span>
            </div>
            @forelse($services as $service)
                <div class="border rounded-3 p-2 mb-2">
                    {{ $service->translations->firstWhere('locale', app()->getLocale())?->name
                        ?? $service->translations->firstWhere('locale', 'ar')?->name
                        ?? $service->code }}
                </div>
            @empty
                <p class="text-secondary mb-0">{{ __('No services are linked to your provider account.') }}</p>
            @endforelse
        </section>
    </div>
    @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, 'emergencies.view'))
    <div class="col-lg-7">
        <section class="panel p-3 p-lg-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h5 fw-bold mb-0">{{ __('Emergency requests') }}</h2>
                @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), $profile, 'emergencies.view'))
                    <a href="{{ route('provider.emergencies.index') }}">{{ __('View all') }}</a>
                @endif
            </div>
            @include('provider.emergencies.table', ['emergencies' => $emergencies])
        </section>
    </div>
    @endif
</div>
@endsection
