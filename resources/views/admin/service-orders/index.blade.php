@extends('layouts.admin')
@section('title', __('Service orders').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<aside class="col-md-3 col-xl-2"><div class="sidebar p-3">
<div class="text-white-50 small px-3 py-2">{{ __('Overview') }}</div>
<a href="{{ route('admin.dashboard') }}">▦ &nbsp;{{ __('Dashboard') }}</a><a href="{{ route('admin.users.index') }}">♙ &nbsp;{{ __('Users') }}</a>
<a href="{{ route('admin.homes.index') }}">⌂ &nbsp;{{ __('Homes') }}</a><a href="{{ route('admin.providers.index') }}">⚒ &nbsp;{{ __('Providers and contracts') }}</a>
<a href="{{ route('admin.packages.index') }}">◫ &nbsp;{{ __('Packages and services') }}</a><a href="{{ route('admin.content.index') }}">◈ &nbsp;{{ __('Resident operations') }}</a>
<a href="{{ route('admin.emergencies.index') }}">🚨 &nbsp;{{ __('Emergency requests') }}</a><a class="active" href="{{ route('admin.service-orders.index') }}">▣ &nbsp;{{ __('Service orders') }}</a>
</div></aside>
<main class="col-md-9 col-xl-10 content">
<div class="mb-4"><div class="small text-secondary">{{ __('Provider assignment and commissions') }}</div><h1 class="h3 fw-bold">{{ __('Service orders') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="panel p-3 p-lg-4"><div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('Order') }}</th><th>{{ __('Resident and home') }}</th><th>{{ __('Service') }}</th><th>{{ __('Status') }}</th><th>{{ __('Assignment and commission') }}</th></tr></thead><tbody>
@forelse($orders as $order)<tr><td><strong>#{{ $order->id }}</strong><div class="small text-secondary">{{ $order->scheduled_at }}</div></td>
<td>{{ $order->resident_name }}<div class="small text-secondary">{{ $order->home_name }}</div></td>
<td>{{ $order->service_name ?? $order->service_code }}<div class="small text-secondary">{{ number_format($order->total_amount,2) }} SAR</div></td>
<td><span class="pill">{{ $order->status_name }}</span></td>
<td style="min-width:350px"><form method="post" action="{{ route('admin.service-orders.update',$order->id) }}" class="d-grid gap-2">@csrf @method('put')
<select class="form-select form-select-sm provider-select" name="provider_profile_id"><option value="">{{ __('Choose provider') }}</option>@foreach($providers as $provider)<option value="{{ $provider->id }}" @selected($order->provider_profile_id===$provider->id)>{{ $provider->company_name }} · {{ $provider->name }}</option>@endforeach</select>
<select class="form-select form-select-sm order-status" name="booking_status_id" required>@foreach($statuses as $status)<option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($order->status_code===$status->code)>{{ $status->name }}</option>@endforeach</select>
<input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control form-control-sm gross-amount" placeholder="{{ __('Final amount for commission') }}">
<input name="note" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}"><button class="btn btn-sm btn-primary">{{ __('Save order update') }}</button></form></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary">{{ __('No service orders found') }}</td></tr>@endforelse
</tbody></table>{{ $orders->links() }}</div></div>
</main></div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.order-status').forEach((select) => {
 const amount = select.closest('form').querySelector('.gross-amount');
 const sync = () => {
   const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
   amount.required = complete;
   amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
 };
 select.addEventListener('change', sync);
 sync();
});
</script>
@endpush
