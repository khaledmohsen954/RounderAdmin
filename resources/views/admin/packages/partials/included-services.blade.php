{{-- المعرفات والكمية فقط تدخل الطلب؛ لا أسعار أو أسماء خدمات يرسلها المتصفح. --}}
<h2 class="h5 mt-4">{{ __('Included services and quantities') }}</h2>
<div id="included-rows">
@foreach(old('services', $package->includedServices->toArray()) as $key => $included)
<div class="row g-2 mb-3 included-row">
<div class="col-md-6"><label class="form-label">{{ __('Service') }}</label><select name="services[{{ $key }}][service_id]" data-field="service_id" class="form-select" required>
@foreach($services as $service)<option value="{{ $service->id }}" @selected($included['service_id'] == $service->id)>{{ $service->translations->firstWhere('locale',app()->getLocale())?->name ?? $service->code }}</option>@endforeach
</select></div>
<div class="col-md-2"><label class="form-label">{{ __('Included quantity') }}</label><input name="services[{{ $key }}][included_quantity]" data-field="included_quantity" class="form-control" type="number" min="1" max="1000" value="{{ $included['included_quantity'] ?? '' }}"></div>
<div class="col-md-3"><label class="form-label">{{ __('Usage limit') }}</label><select name="services[{{ $key }}][is_unlimited]" data-field="is_unlimited" class="form-select"><option value="0" @selected(!$included['is_unlimited'])>{{ __('Limited quantity') }}</option><option value="1" @selected($included['is_unlimited'])>{{ __('Unlimited') }}</option></select></div>
<div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger remove-row" aria-label="{{ __('Remove') }}">×</button></div>
</div>
@endforeach
</div>
<template id="included-template"><div class="row g-2 mb-3 included-row">
<div class="col-md-6"><label class="form-label">{{ __('Service') }}</label><select data-field="service_id" class="form-select" required><option value="">{{ __('Choose service') }}</option>@foreach($services as $service)<option value="{{ $service->id }}">{{ $service->translations->firstWhere('locale',app()->getLocale())?->name ?? $service->code }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">{{ __('Included quantity') }}</label><input data-field="included_quantity" class="form-control" type="number" min="1" max="1000" value="1"></div>
<div class="col-md-3"><label class="form-label">{{ __('Usage limit') }}</label><select data-field="is_unlimited" class="form-select"><option value="0">{{ __('Limited quantity') }}</option><option value="1">{{ __('Unlimited') }}</option></select></div>
<div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-outline-danger remove-row" aria-label="{{ __('Remove') }}">×</button></div>
</div></template>
<button type="button" id="add-included" class="btn btn-outline-primary">{{ __('Add service') }}</button>
@push('scripts')
<script>
(() => {
 const rows = document.getElementById('included-rows');
 const rename = () => rows.querySelectorAll('.included-row').forEach((row, i) => {
  row.querySelectorAll('[data-field]').forEach(field => field.name = 'services[' + i + '][' + field.dataset.field + ']');
  const quantity = row.querySelector('[data-field="included_quantity"]');
  quantity.required = row.querySelector('[data-field="is_unlimited"]').value === '0';
 });
 rows.addEventListener('click', e => { if(e.target.closest('.remove-row')) {e.target.closest('.included-row').remove(); rename();} });
 rows.addEventListener('change', rename);
 document.getElementById('add-included').addEventListener('click', () => {
  rows.appendChild(document.getElementById('included-template').content.cloneNode(true)); rename();
 });
 rename();
})();
</script>
@endpush
