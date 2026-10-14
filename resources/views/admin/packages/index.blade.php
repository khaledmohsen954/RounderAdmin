@extends('layouts.admin')
@section('title', __('Packages and services').' · Rounder')
@section('body')
<div class="container-fluid"><div class="row g-3 p-3 p-lg-4">
<aside class="col-md-3 col-xl-2"><div class="sidebar p-3">
<div class="text-white-50 small px-3 py-2">{{ __('Overview') }}</div>
<a href="{{ route('admin.dashboard') }}">▦ &nbsp;{{ __('Dashboard') }}</a>
<a href="{{ route('admin.users.index') }}">♙ &nbsp;{{ __('Users') }}</a>
<a href="{{ route('admin.homes.index') }}">⌂ &nbsp;{{ __('Homes') }}</a>
<a href="{{ route('admin.providers.index') }}">⚒ &nbsp;{{ __('Providers and contracts') }}</a>
<a class="active" href="{{ route('admin.packages.index') }}">◫ &nbsp;{{ __('Packages and services') }}</a>
<a href="{{ route('admin.content.index') }}">◈ &nbsp;{{ __('Resident operations') }}</a><a href="{{ route('admin.emergencies.index') }}">🚨 &nbsp;{{ __('Emergency requests') }}</a><a href="{{ route('admin.service-orders.index') }}">▣ &nbsp;{{ __('Service orders') }}</a>
</div></aside>
<main class="col-md-9 col-xl-10 content">
<div class="mb-4"><div class="small text-secondary">{{ __('Catalog management') }}</div><h1 class="h3 fw-bold">{{ __('Packages and services') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Create service') }}</h2>
<form method="post" action="{{ route('admin.packages.services.store') }}">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">{{ __('Category') }}</label><select name="service_category_id" class="form-select" required><option value="">{{ __('Choose category') }}</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">{{ __('Service code') }}</label><input name="code" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-3"><label class="form-label">{{ __('Base price') }}</label><input name="base_price" class="form-control" type="number" step=".01" min="0" required></div>
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">{{ __('Save service') }}</button></div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Service name') }} · {{ $label }}</label><input name="name_{{ $locale }}" class="form-control" required maxlength="160"></div>
<div class="col-md-4"><label class="form-label">{{ __('Description') }} · {{ $label }}</label><textarea name="description_{{ $locale }}" class="form-control" rows="2" maxlength="5000"></textarea></div>
@endforeach
</div></form>
</div>

<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Create customer package') }}</h2>
<form method="post" action="{{ route('admin.packages.store') }}" id="package-form">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">{{ __('Package code') }}</label><input name="code" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-3"><label class="form-label">{{ __('Billing cycle') }}</label><select name="billing_cycle_id" class="form-select" required><option value="">{{ __('Choose cycle') }}</option>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}">{{ __($cycle->code === 'semiannual' ? 'Semiannual' : ucfirst($cycle->code)) }} · {{ $cycle->months }} {{ __('months') }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">{{ __('Package price') }}</label><input name="price" class="form-control" type="number" step=".01" min="0" required></div>
<div class="col-md-2"><label class="form-label">{{ __('Currency') }}</label><input name="currency_code" class="form-control" value="SAR" required pattern="[A-Z]{3}" maxlength="3"></div>
<div class="col-md-2"><label class="form-label">{{ __('Duration months') }}</label><input name="duration_months" class="form-control" type="number" min="1" max="120" value="1" required></div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Package name') }} · {{ $label }}</label><input name="name_{{ $locale }}" class="form-control" required maxlength="140"></div>
<div class="col-md-4"><label class="form-label">{{ __('Package details') }} · {{ $label }}</label><textarea name="description_{{ $locale }}" class="form-control" rows="2" maxlength="5000"></textarea></div>
@endforeach
</div>
<div class="d-flex justify-content-between align-items-center mt-4 mb-2"><h3 class="h6 fw-bold mb-0">{{ __('Included services and quantities') }}</h3><button type="button" class="btn btn-sm btn-outline-primary" id="add-service-row">+ {{ __('Add service') }}</button></div>
<div id="package-service-rows"></div>
<div class="d-flex justify-content-end mt-3"><button class="btn btn-primary px-4">{{ __('Save package') }}</button></div>
<template id="service-row-template"><div class="row g-2 align-items-end mb-2 package-service-row">
<div class="col-md-5"><label class="form-label">{{ __('Service') }}</label><select data-name="service_id" class="form-select" required><option value="">{{ __('Choose service') }}</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name ?? $service->code }} · {{ number_format($service->base_price,2) }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">{{ __('Included quantity') }}</label><input data-name="included_quantity" type="number" min="1" max="1000" class="form-control" value="1"></div>
<div class="col-md-3"><label class="form-label">{{ __('Usage limit') }}</label><select data-name="is_unlimited" class="form-select"><option value="0">{{ __('Limited quantity') }}</option><option value="1">{{ __('Unlimited') }}</option></select></div>
<div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-service-row" aria-label="{{ __('Remove') }}">×</button></div>
</div></template>
</form>
</div>

<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Available services') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Service') }}</th><th>{{ __('Category') }}</th><th>{{ __('Base price') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
@forelse($services as $service)<tr><td>{{ $service->id }}</td><td>{{ $service->name ?? $service->code }}<div class="small text-secondary">{{ $service->code }}</div></td><td>{{ $service->category_name ?? '' }}</td><td>{{ number_format($service->base_price,2) }} {{ __('SAR') }}</td><td>{{ $service->is_active ? __('Active') : __('Inactive') }}</td></tr>@empty<tr><td colspan="5">{{ __('No services found') }}</td></tr>@endforelse
</tbody></table></div></div>

<div class="panel p-3 p-lg-4"><h2 class="h5 fw-bold">{{ __('Customer packages') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Package') }}</th><th>{{ __('Cycle') }}</th><th>{{ __('Price') }}</th><th>{{ __('Services included') }}</th><th>{{ __('Duration') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
@forelse($packages as $package)<tr><td>{{ $package->id }}</td><td>{{ $package->name ?? $package->code }}<div class="small text-secondary">{{ $package->code }}</div></td><td>{{ __($package->cycle_code === 'semiannual' ? 'Semiannual' : ucfirst($package->cycle_code)) }}</td><td>{{ number_format($package->price,2) }} {{ $package->currency_code }}</td><td><strong>{{ $package->services_count }}</strong><div class="small text-secondary">@foreach($packageServices->get($package->id,collect()) as $included){{ $included->service_name ?? $included->service_code }} ({{ $included->is_unlimited ? __('Unlimited') : $included->included_quantity }})@if(!$loop->last), @endif @endforeach</div></td><td>{{ $package->duration_months }} {{ __('months') }}</td><td>{{ $package->is_active ? __('Active') : __('Inactive') }}</td></tr>
@empty<tr><td colspan="7">{{ __('No packages found') }}</td>@endforelse
</tbody></table>{{ $packages->links() }}</div></div>
</main></div></div>
<div class="panel p-3 p-lg-4 mt-4"><h2 class="h5 fw-bold">{{ __('Customer subscriptions') }}</h2><div class="table-responsive"><table class="table align-middle"><thead><tr><th>#</th><th>{{ __('Resident') }}</th><th>{{ __('Home') }}</th><th>{{ __('Package') }}</th><th>{{ __('Status') }}</th><th>{{ __('Price') }}</th><th>{{ __('Start date') }}</th><th>{{ __('End date') }}</th></tr></thead><tbody>
@forelse($subscriptions as $subscription)<tr><td>{{ $subscription->id }}</td><td>{{ $subscription->resident_name }}</td><td>{{ $subscription->home_name }}</td><td>{{ $subscription->package_name ?? $subscription->reference }}</td><td><span class="pill">{{ $subscription->status_name }}</span></td><td>{{ number_format($subscription->price_snapshot,2) }} {{ $subscription->currency_code }}</td><td>{{ $subscription->starts_at }}</td><td>{{ $subscription->ends_at }}</td></tr>@empty<tr><td colspan="8">{{ __('No subscriptions found') }}</td></tr>@endforelse
</tbody></table>{{ $subscriptions->links() }}</div></div>
@endsection
@push('scripts')
<script>
(() => {
 const container = document.getElementById('package-service-rows');
 const template = document.getElementById('service-row-template');
 let rowIndex = 0;
 function addRow() {
   const node = template.content.cloneNode(true);
   const row = node.querySelector('.package-service-row');
   row.querySelectorAll('[data-name]').forEach((field) => {
     field.name = 'services[' + rowIndex + '][' + field.dataset.name + ']';
   });
   row.querySelector('[data-name="is_unlimited"]').addEventListener('change', (event) => {
     const quantity = row.querySelector('[data-name="included_quantity"]');
     quantity.disabled = event.target.value === '1';
     if (quantity.disabled) quantity.value = '';
     else if (!quantity.value) quantity.value = '1';
   });
   row.querySelector('.remove-service-row').addEventListener('click', () => row.remove());
   container.appendChild(row);
   rowIndex++;
 }
 document.getElementById('add-service-row').addEventListener('click', addRow);
 addRow();
})();
</script>
@endpush
