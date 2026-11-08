@extends('layouts.admin')
@section('title', __('Customer packages') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Customer packages') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Manage subscription packages, pricing cycles, and included services.') }}</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="pill px-3 py-2 fw-semibold" style="font-size: 0.88rem;">
                <i class="fa-solid fa-boxes-stacked me-1 text-primary"></i>
                {{ __('Total packages') }}: <strong class="text-dark">{{ $packages->total() }}</strong>
            </span>
            @adminCan('admin.packages.create')
            <a class="btn btn-brand d-inline-flex align-items-center gap-2 shadow-sm" href="{{ route('admin.packages.create') }}" style="border-radius: 10px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Create customer package') }}</span>
            </a>
            @endadminCan
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Nav Pills Tabs for Packages, Features & Subscriptions --}}
    <ul class="nav nav-pills mb-4 gap-2">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}" href="{{ route('admin.packages.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-boxes-stacked me-1"></i> {{ __('Customer packages') }}
            </a>
        </li>
        @adminCan('admin.package-features.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.package-features.*') ? 'active' : '' }}" href="{{ route('admin.package-features.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-sliders me-1"></i> {{ __('Package comparison features') }}
            </a>
        </li>
        @endadminCan
        @adminCan('admin.subscriptions.index')
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}" href="{{ route('admin.subscriptions.index') }}" style="border-radius: 10px; font-weight: 600;">
                <i class="fa-solid fa-repeat me-1"></i> {{ __('Customer subscriptions') }}
            </a>
        </li>
        @endadminCan
    </ul>

    {{-- Main Panel --}}
    <div class="panel p-3 p-lg-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="min-width: 780px;">
                <thead class="bg-light-subtle">
                    <tr class="text-secondary small fw-bold">
                        <th style="width: 60px;">#</th>
                        <th>{{ __('Package') }}</th>
                        <th style="width: 170px;">{{ __('Cycle') }}</th>
                        <th style="width: 160px;">{{ __('Price') }}</th>
                        <th>{{ __('Services included') }}</th>
                        <th style="width: 130px;">{{ __('Status') }}</th>
                        <th class="text-end" style="width: 160px;">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td class="text-secondary small font-monospace">#{{ $package->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    @if($package->image_path)
                                        <img class="rounded-3 border flex-shrink-0" width="46" height="46" style="object-fit: cover;" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($package->image_path) }}" alt="{{ $package->name }}">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center rounded-3 flex-shrink-0"
                                             style="width: 46px; height: 46px; background-color: #fff3eb; color: var(--orange, #fb822f);">
                                            <i class="fa-solid fa-box-open" style="font-size: 1.1rem;"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <span class="fw-bold text-dark d-block">{{ $package->name ?? $package->code }}</span>
                                        <code class="text-secondary small font-monospace">{{ $package->code }}</code>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-semibold">
                                    <i class="fa-solid fa-calendar-check me-1"></i>
                                    {{ __($package->cycle_code === 'semiannual' ? 'Semiannual' : ucfirst($package->cycle_code)) }}
                                </span>
                                <div class="text-muted small mt-1">
                                    <i class="fa-regular fa-clock me-1"></i> {{ $package->duration_months }} {{ __('months') }}
                                </div>
                            </td>
                            <td>
                                <div>
                                    @if($package->price_before_discount > $package->price)
                                        <del class="small text-muted d-block font-monospace">{{ number_format($package->price_before_discount, 2) }}</del>
                                    @endif
                                    <span class="fw-bold text-dark font-monospace fs-6">{{ number_format($package->price, 2) }}</span>
                                    <small class="text-muted">{{ $package->currency_code }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill">
                                        <i class="fa-solid fa-wrench me-1"></i>
                                        <strong>{{ $package->services_count }}</strong> {{ __('Services included') }}
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($packageServices->get($package->id, collect()) as $included)
                                        <span class="badge bg-light text-dark border small" style="font-weight: 500;">
                                            {{ $included->service_name ?? $included->service_code }}
                                            <span class="text-primary font-monospace">({{ $included->is_unlimited ? __('Unlimited') : $included->included_quantity }})</span>
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($package->is_active)
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
                                    @adminCan('admin.packages.edit')
                                    <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" href="{{ route('admin.packages.edit', $package->id) }}" style="border-radius: 8px;">
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        <span>{{ __('Edit') }}</span>
                                    </a>
                                    @endadminCan

                                    @adminCan('admin.packages.destroy', 'DELETE')
                                    @include('admin.partials.delete-button', ['action' => route('admin.packages.destroy', $package->id)])
                                    @endadminCan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <div class="mb-2"><i class="fa-solid fa-box-open fa-2x text-muted opacity-50"></i></div>
                                <div class="fw-semibold">{{ __('No packages found') }}</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($packages->hasPages())
            <div class="mt-4 pt-3 border-top">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</main>
@endsection
