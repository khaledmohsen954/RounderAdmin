@extends('layouts.provider')
@section('title', __('Financial accounts'))
@section('body')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1">{{ __('Financial accounts') }}</h1>
        <p class="text-secondary mb-0">{{ __('Order amounts and commissions recorded for your provider account.') }}</p>
    </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4"><div class="metric"><div class="text-secondary">{{ __('Total order amounts') }}</div><div class="value">{{ number_format($totals['gross'], 2) }} <small>{{ __('SAR') }}</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="metric"><div class="text-secondary">{{ __('Platform commissions') }}</div><div class="value">{{ number_format($totals['commission'], 2) }} <small>{{ __('SAR') }}</small></div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="metric"><div class="text-secondary">{{ __('Settled commissions') }}</div><div class="value">{{ number_format($totals['settled'], 2) }} <small>{{ __('SAR') }}</small></div></div></div>
</div>
<section class="panel p-3 p-lg-4">
    <h2 class="h5 fw-bold mb-3">{{ __('Order and commission statement') }}</h2>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Service / request') }}</th><th>{{ __('Order amount') }}</th><th>{{ __('Commission') }}</th><th>{{ __('Status') }}</th><th>{{ __('Date') }}</th></tr></thead>
            <tbody>
            @forelse($entries as $entry)
                @php
                    $serviceName = $entry->booking?->service?->translations?->firstWhere('locale', app()->getLocale())?->name
                        ?? $entry->booking?->service?->code;
                    $requestName = $entry->emergencyRequest?->emergencyType?->translations?->firstWhere('locale', app()->getLocale())?->name
                        ?? $entry->emergencyRequest?->reference;
                @endphp
                <tr>
                    <td>{{ $entry->booking_id ? '#'.$entry->booking_id : ($entry->emergencyRequest?->reference ?? '—') }}</td>
                    <td>{{ $serviceName ?? $requestName ?? '—' }}</td>
                    <td>{{ number_format((float) $entry->gross_amount, 2) }} {{ __('SAR') }}</td>
                    <td>{{ number_format((float) $entry->commission_amount, 2) }} <span class="small text-secondary">({{ number_format((float) $entry->commission_percent_snapshot, 2) }}%)</span></td>
                    <td>{{ $entry->status?->code === 'settled' ? __('Settled') : ($entry->status?->code === 'reversed' ? __('Reversed') : __('Pending')) }}</td>
                    <td>{{ $entry->calculated_at?->translatedFormat('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-secondary py-4">{{ __('No financial entries are recorded yet.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $entries->links() }}</div>
</section>
@endsection
