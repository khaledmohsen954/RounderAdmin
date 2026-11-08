@extends('layouts.provider')
@section('title', __('Create service order'))
@section('body')
<div class="mb-4">
    <a href="{{ route('provider.orders.index') }}" class="text-decoration-none">{{ __('Service orders') }}</a>
    <h1 class="h3 fw-bold mt-2">{{ __('Create service order') }}</h1>
    <p class="text-secondary">{{ __('Choose an existing resident unit and a service offered by your company.') }}</p>
</div>
<form method="post" action="{{ route('provider.orders.store') }}" class="panel p-3 p-lg-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="resident_home_id">{{ __('Resident and unit') }}</label>
            <select class="form-select" name="resident_home_id" id="resident_home_id" required>
                <option value="">{{ __('Select a resident unit') }}</option>
                @foreach($memberships as $membership)
                    <option value="{{ $membership->id }}" data-city-id="{{ $membership->city_id }}" @selected(old('resident_home_id') == $membership->id)>{{ $membership->resident_name }} · {{ $membership->home_name }} · {{ $membership->unit_label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="service_id">{{ __('Service') }}</label>
            <select class="form-select" name="service_id" id="service_id" required>
                <option value="">{{ __('Choose an option') }}</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" data-base-price="{{ $service->base_price }}" @selected(old('service_id') == $service->id)>{{ $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6" id="sub-service-field" hidden>
            <label class="form-label" for="sub_service_id">{{ __('Sub service') }}</label>
            <select class="form-select @error('sub_service_id') is-invalid @enderror" name="sub_service_id" id="sub_service_id" disabled>
                <option value="">{{ __('Choose a sub service') }}</option>
                @foreach($subServices as $subService)
                    <option value="{{ $subService->id }}" data-service-id="{{ $subService->service_id }}" @selected(old('sub_service_id') == $subService->id)>
                        {{ $subService->translations->firstWhere('locale', app()->getLocale())?->name ?? $subService->code }}
                    </option>
                @endforeach
            </select>
            @error('sub_service_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="order-price">{{ __('Price') }} ({{ __('SAR') }})</label>
            <div class="form-control bg-light" id="order-price" aria-live="polite">—</div>
            <div class="form-text" id="order-price-help"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="scheduled_at">{{ __('Scheduled at') }}</label>
            <input class="form-control" type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" required>
        </div>
        <div class="col-12">
            <label class="form-label" for="notes">{{ __('Notes') }}</label>
            <textarea class="form-control" name="notes" id="notes" rows="4" maxlength="2000">{{ old('notes') }}</textarea>
        </div>
        <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" type="submit">{{ __('Create service order') }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('provider.orders.index') }}">{{ __('Cancel') }}</a>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const residentHome = document.getElementById('resident_home_id');
    const service = document.getElementById('service_id');
    const subServiceField = document.getElementById('sub-service-field');
    const subService = document.getElementById('sub_service_id');
    const priceOutput = document.getElementById('order-price');
    const priceHelp = document.getElementById('order-price-help');
    const submitButton = document.querySelector('form[action="{{ route('provider.orders.store') }}"] button[type="submit"]');
    const pricesByCity = @json($pricesByCity);

    const updateSubServicesAndPrice = () => {
        let hasSubServices = false;
        Array.from(subService.options).forEach(option => {
            if (!option.value) return;
            const matchesService = option.dataset.serviceId === service.value;
            option.hidden = !matchesService;
            if (matchesService) {
                hasSubServices = true;
            } else if (option.selected) {
                option.selected = false;
            }
        });

        subServiceField.hidden = !hasSubServices;
        subService.disabled = !hasSubServices;
        subService.required = hasSubServices;
        if (!hasSubServices) subService.value = '';

        const selectedService = service.options[service.selectedIndex];
        let amount = selectedService?.dataset.basePrice || '';
        if (hasSubServices && subService.value) {
            const cityId = residentHome.options[residentHome.selectedIndex]?.dataset.cityId;
            const subServicePrices = pricesByCity[cityId] || {};
            amount = subServicePrices[subService.value] ?? amount;
        }

        priceOutput.textContent = amount === '' ? '—' : Number(amount).toFixed(2);
        priceHelp.textContent = hasSubServices && subService.value
            ? @json(__('The selected sub-service price is used when available; otherwise the service catalogue price applies.'))
            : (hasSubServices
                ? @json(__('Choose a sub service to see its price.'))
                : @json(__('The service price is taken from the current service catalogue.')));
        if (submitButton) submitButton.disabled = false;
    };

    service.addEventListener('change', updateSubServicesAndPrice);
    subService.addEventListener('change', updateSubServicesAndPrice);
    residentHome.addEventListener('change', updateSubServicesAndPrice);
    updateSubServicesAndPrice();
});
</script>
@endpush
