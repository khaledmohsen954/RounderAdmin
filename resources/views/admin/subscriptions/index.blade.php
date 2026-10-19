@extends('layouts.admin')
@section('title', __('Customer subscriptions').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Customer subscriptions') }}</h1>@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4 mt-4"><h2 class="h5 fw-bold">{{ __('Customer subscriptions') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Resident') }}</th><th>{{ __('Home') }}</th><th>{{ __('Package') }}</th><th>{{ __('Status') }}</th><th>{{ __('Price') }}</th><th>{{ __('Start date') }}</th><th>{{ __('End date') }}</th></tr></thead><tbody>
@forelse($subscriptions as $subscription)<tr><td>{{ $subscription->id }}</td><td>{{ $subscription->resident_name }}</td><td>{{ $subscription->home_name }}</td><td>{{ $subscription->package_name ?? $subscription->reference }}</td><td><span class="pill">{{ $subscription->status_name }}</span></td><td>{{ number_format($subscription->price_snapshot,2) }} {{ $subscription->currency_code }}</td><td>{{ $subscription->starts_at }}</td><td>{{ $subscription->ends_at }}</td></tr>@empty<tr><td colspan="8">{{ __('No subscriptions found') }}</td></tr>@endforelse
</tbody></table>{{ $subscriptions->links() }}</div></div>

</main>@endsection
