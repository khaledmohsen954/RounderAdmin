@extends('layouts.admin')
@section('title', __('Create customer package').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Create customer package') }}</h1>@include('admin.partials.feedback')
<div class="panel p-3 p-lg-4 mb-4"><h2 class="h5 fw-bold">{{ __('Create customer package') }}</h2>
@adminCan('admin.packages.store','POST')<form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.store') }}" id="package-form">@csrf
<div class="row g-3">
<div class="col-md-3"><label class="form-label">{{ __('Package code') }}</label><input name="code" class="form-control" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
<div class="col-md-3"><label class="form-label">{{ __('Billing cycle') }}</label><select name="billing_cycle_id" class="form-select" required><option value="">{{ __('Choose cycle') }}</option>@foreach($cycles as $cycle)<option value="{{ $cycle->id }}">{{ __($cycle->code === 'semiannual' ? 'Semiannual' : ucfirst($cycle->code)) }} · {{ $cycle->months }} {{ __('months') }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">{{ __('Customer price before discount') }}</label><input name="price" class="form-control" type="number" step=".01" min="0" required></div>
<div class="col-md-2"><label class="form-label">{{ __('Currency') }}</label><input name="currency_code" class="form-control" value="SAR" required pattern="[A-Z]{3}" maxlength="3"></div>
<div class="col-md-2"><label class="form-label">{{ __('Duration months') }}</label><input name="duration_months" class="form-control" type="number" min="1" max="120" value="1" required></div>
@include('admin.packages.partials.pricing', ['item' => null, 'priceField' => 'price'])
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-4"><label class="form-label">{{ __('Package name') }} · {{ $label }}</label><input name="name_{{ $locale }}" class="form-control" required maxlength="140"></div>
<div class="col-md-4"><label class="form-label">{{ __('Package details') }} · {{ $label }}</label><textarea name="description_{{ $locale }}" class="form-control" rows="2" maxlength="5000"></textarea></div>
@endforeach
</div>
@include('admin.packages.partials.comparison-values', ['featureValues'=>collect()])
<div class="d-flex justify-content-between align-items-center mt-4 mb-2"><h3 class="h6 fw-bold mb-0">{{ __('Included services and quantities') }}</h3><button type="button" class="btn btn-sm btn-outline-primary" id="add-service-row">+ {{ __('Add service') }}</button></div>
<div id="package-service-rows"></div>
<div class="d-flex justify-content-end mt-3"><button class="btn btn-primary px-4">{{ __('Save package') }}</button></div>
<template id="service-row-template"><div class="row g-2 align-items-end mb-2 package-service-row">
<div class="col-md-5"><label class="form-label">{{ __('Service') }}</label><select data-name="service_id" class="form-select" required><option value="">{{ __('Choose service') }}</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->name ?? $service->code }} · {{ number_format($service->base_price,2) }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">{{ __('Included quantity') }}</label><input data-name="included_quantity" type="number" min="1" max="1000" class="form-control" value="1"></div>
<div class="col-md-3"><label class="form-label">{{ __('Usage limit') }}</label><select data-name="is_unlimited" class="form-select"><option value="0">{{ __('Limited quantity') }}</option><option value="1">{{ __('Unlimited') }}</option></select></div>
<div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-service-row" aria-label="{{ __('Remove') }}">×</button></div>
</div></template>
</form>@endadminCan
</div>


</main>@endsection
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
