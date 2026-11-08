@extends('layouts.admin')
@section('title', __('Edit price').' · Rounder')
@section('body')
<main class="container py-4">
    @php
        $locale = app()->getLocale();
        $subName = $price->subService?->translations->firstWhere('locale', $locale)?->name
            ?? $price->subService?->translations->firstWhere('locale', 'ar')?->name
            ?? $price->subService?->code;
        $serviceName = $price->subService?->service?->translations->firstWhere('locale', $locale)?->name
            ?? $price->subService?->service?->translations->firstWhere('locale', 'ar')?->name
            ?? $price->subService?->service?->code;
        $selectedCityIds = old('city_ids', $price->region_id ? \Illuminate\Support\Facades\DB::table('sub_service_price_cities')->where('sub_service_price_id', $price->id)->pluck('city_id')->all() : []);
    @endphp

    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.prices.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Prices') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Edit price') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-tags text-rounder-orange"></i>
                <span>{{ __('Edit price') }}: {{ $price->provider?->company_name ?? '' }} · {{ $subName }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Update price configuration, covered cities, and provider rates.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    <div class="panel p-3 p-lg-4 shadow-sm mb-4" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        {{-- كرت معلومات الخدمة والمزود --}}
        <div class="alert alert-light border mb-4">
            <div class="row g-3">
                <div class="col-md-4">
                    <span class="text-muted d-block small">{{ __('Provider') }}</span>
                    <strong class="fs-6">{{ $price->provider?->company_name ?? '—' }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">{{ __('Service') }}</span>
                    <strong class="fs-6">{{ $serviceName ?? '—' }}</strong>
                </div>
                <div class="col-md-4">
                    <span class="text-muted d-block small">{{ __('Sub service') }}</span>
                    <strong class="fs-6 text-primary">{{ $subName ?? '—' }}</strong>
                </div>
            </div>
        </div>

        <form method="post" action="{{ route('admin.prices.update', $price->id) }}" id="editPriceForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="city_ids_json" id="cityIdsJson">

            <div class="row g-3">
                {{-- المنطقة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-map text-primary me-1"></i> {{ __('Region') }} <span class="text-danger">*</span>
                    </label>
                    <select name="region_id" id="regionSelect" class="form-select @error('region_id') is-invalid @enderror" required>
                        <option value="">-- {{ __('Select region') }} --</option>
                        @foreach($regions as $region)
                            @php
                                $rName = $region->translations->firstWhere('locale', $locale)?->name
                                    ?? $region->translations->firstWhere('locale', 'ar')?->name
                                    ?? $region->code;
                            @endphp
                            <option value="{{ $region->id }}" @selected(old('region_id', (string) $price->region_id) == (string) $region->id)>
                                {{ $rName }}
                            </option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- السعر --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-coins text-warning me-1"></i> {{ __('Price') }} ({{ __('SAR') }}) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="999999.99" name="price" value="{{ old('price', $price->price) }}" class="form-control @error('price') is-invalid @enderror" required>
                        <span class="input-group-text">{{ __('SAR') }}</span>
                    </div>
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- اختيار المدن التابعة للتسعير --}}
                <div class="col-12" id="citiesCardWrapper">
                    <div class="card border">
                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center gap-2 py-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-semibold">
                                    <i class="fa-solid fa-city text-primary me-1"></i> {{ __('Cities') }} <span class="text-danger">*</span>
                                </span>
                                <span class="badge bg-primary rounded-pill" id="selectedCitiesCount">0</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-success" id="btnSelectAll">
                                    <i class="fa-solid fa-check-double me-1"></i> {{ __('Select all') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                                    <i class="fa-solid fa-ban me-1"></i> {{ __('Deselect all') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" id="citySearchInput" class="form-control form-control-sm" placeholder="{{ __('Search city') }}">
                                </div>
                                <div class="col-md-6 text-muted small d-flex align-items-center">
                                    <i class="fa-solid fa-circle-info me-1 text-info"></i>
                                    {{ __('All cities are selected by default. Click on (x) or click any city to exclude it.') }}
                                </div>
                            </div>

                            <div id="citiesLoading" class="text-center py-3 d-none">
                                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                <span class="ms-2 small text-muted">{{ __('Loading...') }}</span>
                            </div>

                            <div id="citiesPlaceholder" class="text-center py-4 text-muted small d-none">
                                <i class="fa-solid fa-arrow-up me-1"></i> {{ __('Please select a region to load its cities.') }}
                            </div>

                            <div id="citiesContainer" class="d-flex flex-wrap gap-2 pt-2" style="max-height: 280px; overflow-y: auto;">
                                {{-- تعبئة المدن هنا --}}
                            </div>

                            @error('city_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- الحالة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-toggle-on text-success me-1"></i> {{ __('Status') }}
                    </label>
                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                        <option value="1" @selected(old('is_active', (string) (int) $price->is_active) === '1')>{{ __('Active') }}</option>
                        <option value="0" @selected(old('is_active', (string) (int) $price->is_active) === '0')>{{ __('Inactive') }}</option>
                    </select>
                    @error('is_active')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-12 mt-4 text-end">
                    <a href="{{ route('admin.prices.index') }}" class="btn btn-outline-secondary px-4 me-2" style="border-radius: 10px;">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                        <i class="fa-solid fa-check"></i>
                        <span>{{ __('Save changes') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const regionSelect = document.getElementById('regionSelect');
    const citiesContainer = document.getElementById('citiesContainer');
    const citiesPlaceholder = document.getElementById('citiesPlaceholder');
    const citiesLoading = document.getElementById('citiesLoading');
    const selectedCitiesCount = document.getElementById('selectedCitiesCount');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const citySearchInput = document.getElementById('citySearchInput');
    const editPriceForm = document.getElementById('editPriceForm');

    const preSelectedCityIds = @json($selectedCityIds);

    regionSelect.addEventListener('change', function () {
        const regionId = this.value;
        if (!regionId) {
            citiesContainer.innerHTML = '';
            citiesPlaceholder.classList.remove('d-none');
            btnSelectAll.disabled = true;
            btnDeselectAll.disabled = true;
            citySearchInput.disabled = true;
            updateCitiesCount();
            return;
        }
        loadCities(regionId, false);
    });

    function loadCities(regionId, isInitialLoad) {
        citiesContainer.innerHTML = '';
        updateCitiesCount();

        citiesPlaceholder.classList.add('d-none');
        citiesLoading.classList.remove('d-none');
        btnSelectAll.disabled = true;
        btnDeselectAll.disabled = true;
        citySearchInput.disabled = true;

        fetch(`{{ url('admin/prices/ajax/region-cities') }}/${regionId}`)
            .then(res => res.json())
            .then(cities => {
                citiesLoading.classList.add('d-none');
                renderCities(cities, isInitialLoad);
                btnSelectAll.disabled = false;
                btnDeselectAll.disabled = false;
                citySearchInput.disabled = false;
            })
            .catch(() => {
                citiesLoading.classList.add('d-none');
            });
    }

    function renderCities(cities, isInitialLoad) {
        citiesContainer.innerHTML = '';
        if (!cities.length) {
            citiesContainer.innerHTML = '<span class="text-muted small p-2">{{ __('No cities in this region') }}</span>';
            updateCitiesCount();
            return;
        }

        const fragment = document.createDocumentFragment();
        cities.forEach(city => {
            const isSelected = isInitialLoad && preSelectedCityIds.length
                ? (preSelectedCityIds.includes(Number(city.id)) || preSelectedCityIds.includes(String(city.id)))
                : true;

            const chip = document.createElement('div');
            chip.className = `city-chip badge rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 border ${isSelected ? 'bg-primary text-white border-primary' : 'bg-light text-secondary border-secondary'}`;
            chip.style.cursor = 'pointer';
            chip.dataset.id = city.id;
            chip.dataset.name = city.name.toLowerCase();

            chip.innerHTML = `
                <span class="city-name">${city.name}</span>
                <i class="fa-solid ${isSelected ? 'fa-xmark' : 'fa-plus'} remove-icon ms-1" style="font-size: 0.85rem;"></i>
                <input type="checkbox" value="${city.id}" ${isSelected ? 'checked' : ''} class="d-none city-checkbox">
            `;

            chip.addEventListener('click', function () {
                toggleCity(chip);
            });

            fragment.appendChild(chip);
        });

        citiesContainer.appendChild(fragment);
        updateCitiesCount();
    }

    function toggleCity(chip) {
        const checkbox = chip.querySelector('.city-checkbox');
        const icon = chip.querySelector('.remove-icon');
        checkbox.checked = !checkbox.checked;

        if (checkbox.checked) {
            chip.classList.remove('bg-light', 'text-secondary', 'border-secondary');
            chip.classList.add('bg-primary', 'text-white', 'border-primary');
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-xmark');
        } else {
            chip.classList.remove('bg-primary', 'text-white', 'border-primary');
            chip.classList.add('bg-light', 'text-secondary', 'border-secondary');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-plus');
        }
        updateCitiesCount();
    }

    btnSelectAll.addEventListener('click', function () {
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const checkbox = chip.querySelector('.city-checkbox');
            const icon = chip.querySelector('.remove-icon');
            checkbox.checked = true;
            chip.classList.remove('bg-light', 'text-secondary', 'border-secondary');
            chip.classList.add('bg-primary', 'text-white', 'border-primary');
            icon.classList.remove('fa-plus');
            icon.classList.add('fa-xmark');
        });
        updateCitiesCount();
    });

    btnDeselectAll.addEventListener('click', function () {
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const checkbox = chip.querySelector('.city-checkbox');
            const icon = chip.querySelector('.remove-icon');
            checkbox.checked = false;
            chip.classList.remove('bg-primary', 'text-white', 'border-primary');
            chip.classList.add('bg-light', 'text-secondary', 'border-secondary');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-plus');
        });
        updateCitiesCount();
    });

    citySearchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();
        citiesContainer.querySelectorAll('.city-chip').forEach(chip => {
            const name = chip.dataset.name;
            if (!term || name.includes(term)) {
                chip.classList.remove('d-none');
            } else {
                chip.classList.add('d-none');
            }
        });
    });

    function updateCitiesCount() {
        const checked = citiesContainer.querySelectorAll('.city-checkbox:checked').length;
        selectedCitiesCount.textContent = checked;
    }

    editPriceForm.addEventListener('submit', function (e) {
        if (!regionSelect.value) {
            e.preventDefault();
            alert('{{ __('Please select a region.') }}');
            return false;
        }

        const checkedBoxes = citiesContainer.querySelectorAll('.city-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => parseInt(cb.value));
        if (ids.length === 0) {
            e.preventDefault();
            alert('{{ __('Please select at least one city.') }}');
            return false;
        }
        document.getElementById('cityIdsJson').value = JSON.stringify(ids);
    });

    // تحميل الحالة الأولية
    if (regionSelect.value) {
        loadCities(regionSelect.value, true);
    }
});
</script>
@endsection
