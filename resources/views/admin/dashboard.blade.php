@extends('layouts.admin')
@section('title',__('Dashboard').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">

<main class="col-12 content">
<div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-4"><div><div class="small text-secondary">{{ __('Live metrics') }}</div><h1 class="h3 fw-bold mb-1">{{ __('Dashboard') }}</h1><p class="text-secondary mb-0">{{ __('Metrics are queried from MySQL records.') }}</p></div><span class="pill">{{ now()->translatedFormat('d F Y') }}</span></div>
<div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3 mb-4">
@if(array_key_exists('residents',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Residents') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Residents') }}</span><span class="kpi-icon"><i class="fa-solid fa-users" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['residents'],0) }}</div>
</article></div>@endif
@if(array_key_exists('providers',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Approved providers') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Approved providers') }}</span><span class="kpi-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['providers'],0) }}</div>
</article></div>@endif
@if(array_key_exists('bookings',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('All bookings') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('All bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-calendar-check" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['bookings'],0) }}</div>
</article></div>@endif
@if(array_key_exists('pending_bookings',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Pending bookings') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending bookings') }}</span><span class="kpi-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['pending_bookings'],0) }}</div>
</article></div>@endif
@if(array_key_exists('revenue',$stats))
<div class="col"><article class="kpi-card financial" aria-label="{{ __('Collected payments') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Collected payments') }}</span><span class="kpi-icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['revenue'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
</article></div>@endif
@if(array_key_exists('linked_homes',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Linked homes') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Linked homes') }}</span><span class="kpi-icon"><i class="fa-solid fa-building" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['linked_homes'],0) }}</div>
</article></div>@endif
@if(array_key_exists('open_reports',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Open facility reports') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Open facility reports') }}</span><span class="kpi-icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['open_reports'],0) }}</div>
</article></div>@endif
@if(array_key_exists('community_posts',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Community posts') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Community posts') }}</span><span class="kpi-icon"><i class="fa-solid fa-comments" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['community_posts'],0) }}</div>
</article></div>@endif
@if(array_key_exists('active_initiatives',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Active initiatives') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active initiatives') }}</span><span class="kpi-icon"><i class="fa-solid fa-bullhorn" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['active_initiatives'],0) }}</div>
</article></div>@endif
@if(array_key_exists('pending_emergencies',$stats))
<div class="col"><article class="kpi-card urgent" aria-label="{{ __('Pending emergency requests') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Pending emergency requests') }}</span><span class="kpi-icon"><i class="fa-solid fa-truck-medical" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['pending_emergencies'],0) }}</div>
</article></div>@endif
@if(array_key_exists('active_subscriptions',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Active subscriptions') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Active subscriptions') }}</span><span class="kpi-icon"><i class="fa-solid fa-repeat" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['active_subscriptions'],0) }}</div>
</article></div>@endif
@if(array_key_exists('available_packages',$stats))
<div class="col"><article class="kpi-card " aria-label="{{ __('Available packages') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Available packages') }}</span><span class="kpi-icon"><i class="fa-solid fa-box-open" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['available_packages'],0) }}</div>
</article></div>@endif
@if(array_key_exists('commission_total',$stats))
<div class="col"><article class="kpi-card financial" aria-label="{{ __('Platform commissions') }}">
<div class="d-flex justify-content-between align-items-start gap-3"><span class="kpi-label">{{ __('Platform commissions') }}</span><span class="kpi-icon"><i class="fa-solid fa-coins" aria-hidden="true"></i></span></div>
<div class="kpi-value">{{ number_format($stats['commission_total'],2) }} <span class="kpi-currency">{{ __('SAR') }}</span></div>
</article></div>@endif
</div>
@adminCan('admin.service-orders.index')
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold mb-3">{{ __('Recent bookings') }}</h2><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>#</th><th>{{ __('Resident') }}</th><th>{{ __('Service') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead><tbody>
@forelse($recent as $booking)<tr><td>{{ $booking->id }}</td><td>{{ $booking->resident->name }}</td><td>{{ $booking->service->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->service->code }}</td><td><span class="pill">{{ $booking->status->translations->firstWhere('locale',app()->getLocale())?->name ?? $booking->status->code }}</span></td><td>{{ $booking->created_at->format('Y-m-d') }}</td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-4">{{ __('No bookings yet') }}</td></tr>@endforelse</tbody></table></div></div>
@endadminCan
</main></div></div>
@endsection
