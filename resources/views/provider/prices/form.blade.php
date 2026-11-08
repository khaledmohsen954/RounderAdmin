@extends('layouts.provider')
@section('title', $price ? __('Edit and resubmit price') : __('Add price'))
@section('body')
<div class="mb-4">
    <h1 class="h3 fw-bold">{{ $price ? __('Edit and resubmit price') : __('Submit a service price') }}</h1>
    <p class="text-secondary mb-0">{{ __('Prices are sent to the administration for approval before they become available to customers.') }}</p>
</div>
<section class="panel p-3 p-lg-4">
    <form method="post" action="{{ $price ? route('provider.prices.update', $price->id) : route('provider.prices.store') }}">
        @csrf
        @if($price) @method('put') @endif
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="service_id">{{ __('Service') }}</label>
                <select class="form-select" id="service_id" name="service_id" required>
                    <option value="">{{ __('Choose a service') }}</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @selected(old('service_id', $price?->subService?->service_id) == $service->id)>
                            {{ $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->translations->firstWhere('locale', 'ar')?->name ?? $service->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="sub_service_id">{{ __('Sub service') }}</label>
                <select class="form-select" id="sub_service_id" name="sub_service_id" required>
                    <option value="">{{ __('Choose a sub service') }}</option>
                    @foreach($subServices as $subService)
                        <option value="{{ $subService->id }}" data-service-id="{{ $subService->service_id }}" @selected(old('sub_service_id', $price?->sub_service_id) == $subService->id)>
                            {{ $subService->translations->firstWhere('locale', app()->getLocale())?->name ?? $subService->translations->firstWhere('locale', 'ar')?->name ?? $subService->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="region_id">{{ __('Region') }}</label>
                <select class="form-select" id="region_id" name="region_id" required>
                    <option value="">{{ __('Choose a region') }}</option>
                    @foreach($regions as $region)
                        <option value="{{ $region->id }}" @selected(old('region_id', $price?->region_id) == $region->id)>
                            {{ $region->translations->firstWhere('locale', app()->getLocale())?->name ?? $region->translations->firstWhere('locale', 'ar')?->name ?? $region->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="price">{{ __('Price') }} ({{ __('SAR') }})</label>
                <input class="form-control" id="price" name="price" type="number" min="0" max="999999.99" step="0.01" value="{{ old('price', $price?->price) }}" required>
            </div>
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                    <label class="form-label mb-0">{{ __('Covered cities') }}</label>
                    <button class="btn btn-sm btn-outline-secondary" id="select-region-cities" type="button" disabled>{{ __('Select all') }}</button>
                </div>
                @php($selectedCities = collect(old('city_ids', $price?->cities->pluck('id')->all() ?? []))->map(fn ($id) => (string) $id)->all())
                <div id="city-options" class="d-flex flex-wrap gap-2">
                    @foreach($regions as $region)
                        @foreach($region->cities as $city)
                            <label class="city-option border rounded-3 px-3 py-2" data-region-id="{{ $region->id }}">
                                <input class="form-check-input me-1" type="checkbox" name="city_ids[]" value="{{ $city->id }}" @checked(in_array((string) $city->id, $selectedCities, true))>
                                {{ $city->translations->firstWhere('locale', app()->getLocale())?->name ?? $city->translations->firstWhere('locale', 'ar')?->name ?? $city->code }}
                            </label>
                        @endforeach
                    @endforeach
                </div>
                @if($regions->isEmpty())
                    <div class="text-secondary">{{ __('No active regions are available.') }}</div>
                @endif
            </div>
            <div class="col-12 d-flex gap-2">
                <button class="btn btn-orange" type="submit">{{ $price ? __('Resubmit for approval') : __('Submit for approval') }}</button>
                <a class="btn btn-outline-secondary" href="{{ route('provider.prices.index') }}">{{ __('Cancel') }}</a>
            </div>
        </div>
    </form>
</section>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const service = document.getElementById('service_id');
    const subService = document.getElementById('sub_service_id');
    const region = document.getElementById('region_id');
    const selectRegionCities = document.getElementById('select-region-cities');
    const filterSubServices = () => {
        let selected = false;
        Array.from(subService.options).forEach(option => {
            if (!option.value) return;
            const visible = option.dataset.serviceId === service.value;
            option.hidden = !visible;
            if (!visible && option.selected) option.selected = false;
            if (visible && option.selected) selected = true;
        });
        if (!selected) subService.value = '';
    };
    const filterCities = (clear = false) => {
        let hasCities = false;
        document.querySelectorAll('.city-option').forEach(label => {
            const input = label.querySelector('input');
            const visible = label.dataset.regionId === region.value;
            label.hidden = !visible;
            input.disabled = !visible;
            if (clear && !visible) input.checked = false;
            if (visible) hasCities = true;
        });
        selectRegionCities.disabled = !region.value || !hasCities;
    };
    selectRegionCities.addEventListener('click', () => {
        document.querySelectorAll('.city-option').forEach(label => {
            if (label.dataset.regionId === region.value) {
                label.querySelector('input').checked = true;
            }
        });
    });
    service.addEventListener('change', filterSubServices);
    region.addEventListener('change', () => filterCities(true));
    filterSubServices();
    filterCities();
});
</script>
@endsection
