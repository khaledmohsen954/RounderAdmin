@extends('layouts.provider')
@section('title', __('Prices'))
@section('body')
<div class="mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h1 class="h3 fw-bold">{{ __('Prices') }}</h1>
            <p class="text-secondary mb-0">{{ __('Submit service prices by region for admin review.') }}</p>
        </div>
        @if(app(\App\Modules\Providers\Services\ProviderPortalService::class)->allows(auth('provider')->user(), app(\App\Modules\Providers\Services\ProviderPortalService::class)->profileFor(auth('provider')->user()), 'prices.manage'))
            <a class="btn btn-orange" href="{{ route('provider.prices.create') }}">+ {{ __('Add price') }}</a>
        @endif
    </div>
</div>
<section class="panel p-3 p-lg-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>{{ __('Service') }}</th><th>{{ __('Sub service') }}</th><th>{{ __('Region') }}</th><th>{{ __('Cities') }}</th><th>{{ __('Price') }}</th><th>{{ __('Approval') }}</th><th>{{ __('Status') }}</th><th></th></tr></thead>
            <tbody>
            @forelse($prices as $price)
                @php
                    $subService = $price->subService;
                    $region = $price->region;
                @endphp
                <tr>
                    <td>{{ $subService?->service?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $subService?->service?->code ?? '—' }}</td>
                    <td>{{ $subService?->translations?->firstWhere('locale', app()->getLocale())?->name ?? $subService?->code ?? '—' }}</td>
                    <td>{{ $region?->translations?->firstWhere('locale', app()->getLocale())?->name ?? __('All regions') }}</td>
                    <td>{{ $price->cities->map(fn ($city) => $city->translations->firstWhere('locale', app()->getLocale())?->name ?? $city->code)->filter()->join('، ') ?: '—' }}</td>
                    <td>{{ number_format((float) $price->price, 2) }}</td>
                    <td>
                        @php($approvalLabels = ['pending' => __('Pending approval'), 'approved' => __('Approved'), 'rejected' => __('Rejected')])
                        <span class="badge {{ $price->approval_status === 'approved' ? 'text-bg-success' : ($price->approval_status === 'rejected' ? 'text-bg-danger' : 'text-bg-warning') }}">
                            {{ $approvalLabels[$price->approval_status] ?? $price->approval_status }}
                        </span>
                        @if($price->rejection_reason)
                            <div class="small text-danger mt-1">{{ $price->rejection_reason }}</div>
                        @endif
                    </td>
                    <td><span class="badge {{ $price->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $price->is_active ? __('Active') : __('Inactive') }}</span></td>
                    <td>
                        @if($price->approval_status === 'rejected')
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('provider.prices.edit', $price->id) }}">{{ __('Edit and resubmit') }}</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-secondary py-4">{{ __('No prices have been submitted for your account.') }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $prices->links() }}</div>
</section>
@endsection
