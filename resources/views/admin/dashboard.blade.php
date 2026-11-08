@extends('layouts.admin')
@section('title',__('Dashboard').' · Rounder')
@section('body')
<div class="container-fluid px-2 px-sm-3 px-lg-4 py-3 py-lg-4">
    <div class="row g-3">
        <main class="col-12 content">
            {{-- Header --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge rounded-pill" style="background: rgba(251, 130, 47, 0.1); color: #d05e0c; border: 1px solid rgba(251, 130, 47, 0.25); font-size: 0.78rem; font-weight: 700; padding: 0.35rem 0.8rem;">
                            <i class="fa-solid fa-bolt me-1 text-rounder-orange"></i>{{ __('Live metrics') }}
                        </span>
                    </div>
                    <h1 class="h3 fw-bold mb-1 text-dark">{{ __('Dashboard') }}</h1>
                    <p class="text-secondary small mb-0">{{ __('Metrics are queried from MySQL records.') }}</p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @adminCan('admin.service-orders.create')
                    <a class="btn btn-brand d-inline-flex align-items-center gap-1.5 shadow-sm" href="{{ route('admin.service-orders.create') }}" style="border-radius: 11px; padding: 0.5rem 1.15rem; font-weight: 700;">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ __('Create service order') }}</span>
                    </a>
                    @endadminCan
                </div>
            </div>

            {{-- KPI Cards --}}
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
                @if(array_key_exists('residents',$stats))
                <div class="col"><a href="{{ route('admin.residents.index') }}" class="kpi-card" aria-label="{{ __('Residents') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Residents') }}</span><span class="kpi-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['residents'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('providers',$stats))
                <div class="col"><a href="{{ route('admin.providers.index') }}" class="kpi-card" aria-label="{{ __('Approved providers') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Approved providers') }}</span><span class="kpi-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['providers'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('bookings',$stats))
                <div class="col"><a href="{{ route('admin.service-orders.index') }}" class="kpi-card" aria-label="{{ __('All bookings') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('All bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['bookings'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('pending_bookings',$stats))
                <div class="col"><a href="{{ route('admin.service-orders.index') }}" class="kpi-card" aria-label="{{ __('Pending bookings') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['pending_bookings'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('revenue',$stats))
                <div class="col"><article class="kpi-card" aria-label="{{ __('Collected payments') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Collected payments') }}</span><span class="kpi-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['revenue'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
                </article></div>@endif
                @if(array_key_exists('linked_homes',$stats))
                <div class="col"><a href="{{ route('admin.realestate.index') }}" class="kpi-card" aria-label="{{ __('Linked homes') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Linked homes') }}</span><span class="kpi-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['linked_homes'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('open_reports',$stats))
                <div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Open facility reports') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Open facility reports') }}</span><span class="kpi-icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['open_reports'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('community_posts',$stats))
                <div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Community posts') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Community posts') }}</span><span class="kpi-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['community_posts'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('active_initiatives',$stats))
                <div class="col"><a href="{{ route('admin.content.index') }}" class="kpi-card" aria-label="{{ __('Active initiatives') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active initiatives') }}</span><span class="kpi-icon"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['active_initiatives'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('pending_emergencies',$stats))
                <div class="col"><a href="{{ route('admin.emergencies.index') }}" class="kpi-card" aria-label="{{ __('Pending emergency requests') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending emergency requests') }}</span><span class="kpi-icon"><i class="fa-solid fa-truck-medical" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['pending_emergencies'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('active_subscriptions',$stats))
                <div class="col"><a href="{{ route('admin.subscriptions.index') }}" class="kpi-card" aria-label="{{ __('Active subscriptions') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active subscriptions') }}</span><span class="kpi-icon"><i class="fa-solid fa-repeat" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['active_subscriptions'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('available_packages',$stats))
                <div class="col"><a href="{{ route('admin.packages.index') }}" class="kpi-card" aria-label="{{ __('Available packages') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Available packages') }}</span><span class="kpi-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['available_packages'],0) }}</div>
                </a></div>@endif
                @if(array_key_exists('commission_total',$stats))
                <div class="col"><article class="kpi-card" aria-label="{{ __('Platform commissions') }}">
                    <div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Platform commissions') }}</span><span class="kpi-icon"><i class="fa-solid fa-coins" aria-hidden="true"></i></span></div>
                    <div class="kpi-value">{{ number_format($stats['commission_total'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
                </article></div>@endif
            </div>

            {{-- Recent Bookings --}}
            @adminCan('admin.service-orders.index')
            <div class="panel p-3 p-lg-4 mb-4" style="border-radius: 16px;">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1 text-dark">{{ __('Recent bookings') }}</h2>
                        <div class="small text-secondary">{{ __('Latest activity across the system') }}</div>
                    </div>
                    <a href="{{ route('admin.service-orders.index') }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5" style="border-radius: 9px; font-weight: 600; padding: 0.4rem 0.95rem;">
                        <span>{{ __('View all') }}</span>
                        <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }}"></i>
                    </a>
                </div>

                {{-- 1. Mobile Cards View (Screens < 768px) --}}
                <div class="d-md-none d-flex flex-column gap-2.5">
                    @forelse($recent as $booking)
                        @php
                            $stCode = $booking->status->code ?? 'pending';
                            $stColor = match($stCode) {
                                'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                                'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                                'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                                default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                            };
                        @endphp
                        <div class="p-3 border rounded-3 bg-white shadow-sm" style="border-color: #e5ede9 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge text-white" style="background: var(--petrol); font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 7px; font-weight: 700;">
                                    #{{ $booking->id }}
                                </span>
                                <span class="badge rounded-pill d-inline-flex align-items-center gap-1.5" style="background: {{ $stColor['bg'] }}; color: {{ $stColor['color'] }}; border: 1px solid {{ $stColor['border'] }}; font-size: 0.76rem; font-weight: 600; padding: 0.3rem 0.7rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $stColor['dot'] }};"></span>
                                    <span>{{ $booking->status->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->status->code }}</span>
                                </span>
                            </div>

                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-size: 0.85rem;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-bold text-dark fs-6 text-truncate">{{ $booking->resident->name }}</div>
                                    @if($booking->home)
                                        <div class="small text-secondary text-truncate" style="font-size: 0.74rem;">
                                            <i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $booking->home->name }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top gap-2" style="border-color: #edf3f0 !important;">
                                <div class="d-flex align-items-center gap-1.5 small text-secondary text-truncate">
                                    <i class="fa-solid fa-screwdriver-wrench text-rounder-orange flex-shrink-0"></i>
                                    <span class="fw-semibold text-dark text-truncate">{{ $booking->service->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->service->code }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <span class="small text-muted" style="font-size: 0.74rem;"><i class="fa-regular fa-calendar me-1"></i>{{ $booking->created_at->format('m-d') }}</span>
                                    <a href="{{ route('admin.service-orders.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1 py-1 px-2" style="border-radius: 7px; font-size: 0.75rem;">
                                        <i class="fa-solid fa-eye text-rounder-orange"></i>
                                        <span>{{ __('View') }}</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-secondary py-4">
                            <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary opacity-50"></i>
                            <div>{{ __('No bookings yet') }}</div>
                        </div>
                    @endforelse
                </div>

                {{-- 2. Desktop Table View (Screens >= 768px) --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table align-middle mb-0" style="min-width: 680px;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eef2f1;">
                                <th style="width: 70px;">#</th>
                                <th>{{ __('Resident') }}</th>
                                <th>{{ __('Service') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Date') }}</th>
                                <th class="text-end" style="width: 140px;">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent as $booking)
                            <tr style="border-bottom: 1px solid #edf3f0;">
                                <td class="py-3">
                                    <a href="{{ route('admin.service-orders.show', $booking->id) }}" class="badge text-decoration-none" style="background: var(--petrol); color: #fff; font-size: 0.8rem; padding: 0.35rem 0.65rem; border-radius: 7px; font-weight: 700;">
                                        #{{ $booking->id }}
                                    </a>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; background: rgba(2, 51, 62, 0.08); color: var(--petrol); font-size: 0.85rem;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark fs-6">{{ $booking->resident->name }}</div>
                                            @if($booking->home)
                                                <div class="small text-secondary" style="font-size: 0.74rem;">
                                                    <i class="fa-solid fa-house-chimney text-rounder-orange me-1"></i>{{ $booking->home->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; background: rgba(251, 130, 47, 0.1); color: var(--orange); font-size: 0.8rem;">
                                            <i class="fa-solid fa-screwdriver-wrench"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $booking->service->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->service->code }}</span>
                                    </div>
                                </td>
                                <td class="py-3">
                                    @php
                                        $stCode = $booking->status->code ?? 'pending';
                                        $stColor = match($stCode) {
                                            'completed' => ['bg' => '#eef8f4', 'color' => '#0e6c51', 'border' => '#bde4d5', 'dot' => '#10b981'],
                                            'cancelled' => ['bg' => '#fdf2f2', 'color' => '#b32a2a', 'border' => '#f4c7c7', 'dot' => '#ef4444'],
                                            'in_progress', 'accepted' => ['bg' => '#e8f4f8', 'color' => '#035368', 'border' => '#bce3eb', 'dot' => '#06b6d4'],
                                            default => ['bg' => '#fff8ec', 'color' => '#b45309', 'border' => '#fed7aa', 'dot' => '#f59e0b'],
                                        };
                                    @endphp
                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-2" style="background: {{ $stColor['bg'] }}; color: {{ $stColor['color'] }}; border: 1px solid {{ $stColor['border'] }}; font-size: 0.78rem; font-weight: 600; padding: 0.35rem 0.75rem;">
                                        <span style="width: 6px; height: 6px; border-radius: 50%; background: {{ $stColor['dot'] }};"></span>
                                        <span>{{ $booking->status->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->status->code }}</span>
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="small text-secondary fw-medium" style="font-size: 0.78rem;">
                                        <i class="fa-regular fa-calendar text-rounder-orange me-1"></i>{{ $booking->created_at->format('Y-m-d') }}
                                    </div>
                                </td>
                                <td class="py-3 text-end">
                                    <a href="{{ route('admin.service-orders.show', $booking->id) }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1.5" title="{{ __('View details') }}" style="border-radius: 8px; font-weight: 600; padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                        <i class="fa-solid fa-eye text-rounder-orange"></i>
                                        <span>{{ __('View details') }}</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">
                                    <i class="fa-regular fa-folder-open d-block fs-2 mb-2 text-secondary opacity-50"></i>
                                    <div>{{ __('No bookings yet') }}</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endadminCan
        </main>
    </div>
</div>
@endsection
