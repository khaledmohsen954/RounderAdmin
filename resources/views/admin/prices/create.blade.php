@extends('layouts.admin')
@section('title', __('Add price').' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.prices.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Prices') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Add price') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-tags text-rounder-orange"></i>
                <span>{{ __('Add price') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Configure service pricing by provider, sub-service, and covered geographic cities.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    <div class="panel p-3 p-lg-4 shadow-sm mb-4" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        <form method="post" action="{{ route('admin.prices.store') }}" id="priceForm">
            @csrf

            <div class="row g-3">
                {{-- 1. مزود الخدمة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-user-tie text-primary me-1"></i> {{ __('Provider') }} <span class="text-danger">*</span>
                    </label>
                    <select name="provider_profile_id" id="providerSelect" class="form-select @error('provider_profile_id') is-invalid @enderror" required>
                        <option value="">-- {{ __('Select provider') }} --</option>
                        @foreach($providers as $provider)
                            <option value="{{ $provider->id }}" @selected(old('provider_profile_id') == $provider->id)>
                                {{ $provider->company_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('provider_profile_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 2. الخدمة الرئيسية --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-screwdriver-wrench text-primary me-1"></i> {{ __('Service') }} <span class="text-danger">*</span>
                    </label>
                    <select name="service_id" id="serviceSelect" class="form-select @error('service_id') is-invalid @enderror" required disabled>
                        <option value="">-- {{ __('Select provider first') }} --</option>
                    </select>
                    @error('service_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 3. الخدمة الفرعية --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-list-check text-primary me-1"></i> {{ __('Sub service') }} <span class="text-danger">*</span>
                    </label>
                    <select name="sub_service_id" id="subServiceSelect" class="form-select @error('sub_service_id') is-invalid @enderror" required disabled>
                        <option value="">-- {{ __('Select service first') }} --</option>
                    </select>
                    @error('sub_service_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 4. المنطقة --}}
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
                            <option value="{{ $region->id }}" @selected(old('region_id') == $region->id)>
                                {{ $rName }}
                            </option>
                        @endforeach
                    </select>
                    @error('region_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 5. اختيار المدن (تحديد الكل تلقائياً مع إمكانية حذف أي مدينة) --}}
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
                                <button type="button" class="btn btn-sm btn-outline-success" id="btnSelectAll" disabled>
                                    <i class="fa-solid fa-check-double me-1"></i> {{ __('Select all') }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll" disabled>
                                    <i class="fa-solid fa-ban me-1"></i> {{ __('Deselect all') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <input type="text" id="citySearchInput" class="form-control form-control-sm" placeholder="{{ __('Search city') }}" disabled>
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

                            <div id="citiesPlaceholder" class="text-center py-4 text-muted small">
                                <i class="fa-solid fa-arrow-up me-1"></i> {{ __('Please select a region to load its cities.') }}
                            </div>

                            <div id="citiesContainer" class="d-flex flex-wrap gap-2 pt-2" style="max-height: 280px; overflow-y: auto;">
                                {{-- يتم تعبئة بطاقات المدن هنا ديناميكياً بواسطة JavaScript --}}
                            </div>
                            <input type="hidden" name="city_ids_json" id="cityIdsJson">

                            @error('city_ids')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- 6. السعر --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-coins text-warning me-1"></i> {{ __('Price') }} ({{ __('SAR') }}) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0" max="999999.99" name="price" value="{{ old('price') }}" class="form-control @error('price') is-invalid @enderror" placeholder="0.00" required>
                        <span class="input-group-text">{{ __('SAR') }}</span>
                    </div>
                    @error('price')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                {{-- 7. الحالة --}}
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-toggle-on text-success me-1"></i> {{ __('Status') }}
                    </label>
                    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                        <option value="1" @selected(old('is_active', '1') == '1')>{{ __('Active') }}</option>
                        <option value="0" @selected(old('is_active') === '0')>{{ __('Inactive') }}</option>
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
                        <span>{{ __('Save price') }}</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const providerSelect = document.getElementById('providerSelect');
    const serviceSelect = document.getElementById('serviceSelect');
    const subServiceSelect = document.getElementById('subServiceSelect');
    const regionSelect = document.getElementById('regionSelect');

    const citiesContainer = document.getElementById('citiesContainer');
    const citiesPlaceholder = document.getElementById('citiesPlaceholder');
    const citiesLoading = document.getElementById('citiesLoading');
    const selectedCitiesCount = document.getElementById('selectedCitiesCount');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const citySearchInput = document.getElementById('citySearchInput');

    const oldServiceId = "{{ old('service_id') }}";
    const oldSubServiceId = "{{ old('sub_service_id') }}";
    const oldCityIds = @json(old('city_ids', []));

    let currentCitiesList = [];

    // 1. عند تغيير مزود الخدمة -> جلب خدماته
    providerSelect.addEventListener('change', function () {
        const providerId = this.value;
        serviceSelect.innerHTML = '<option value="">-- {{ __('Select service') }} --</option>';
        subServiceSelect.innerHTML = '<option value="">-- {{ __('Select service first') }} --</option>';
        subServiceSelect.disabled = true;

        if (!providerId) {
            serviceSelect.disabled = true;
            return;
        }

        serviceSelect.disabled = true;
        fetch(`{{ url('admin/prices/ajax/provider-services') }}/${providerId}`)
            .then(res => res.json())
            .then(services => {
                serviceSelect.innerHTML = '<option value="">-- {{ __('Select service') }} --</option>';
                services.forEach(s => {
                    const opt = document.createElement('option');
                    opt.value = s.id;
                    opt.textContent = s.name;
                    if (oldServiceId && s.id == oldServiceId) {
                        opt.selected = true;
                    }
                    serviceSelect.appendChild(opt);
                });
                serviceSelect.disabled = false;
                if (serviceSelect.value) {
                    serviceSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(() => {
                serviceSelect.disabled = false;
            });
    });

    // 2. عند تغيير الخدمة الرئيسية -> جلب الخدمات الفرعية
    serviceSelect.addEventListener('change', function () {
        const serviceId = this.value;
        subServiceSelect.innerHTML = '<option value="">-- {{ __('Select sub service') }} --</option>';

        if (!serviceId) {
            subServiceSelect.disabled = true;
            return;
        }

        subServiceSelect.disabled = true;
        fetch(`{{ url('admin/prices/ajax/sub-services') }}/${serviceId}`)
            .then(res => res.json())
            .then(subServices => {
                subServiceSelect.innerHTML = '<option value="">-- {{ __('Select sub service') }} --</option>';
                subServices.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.id;
                    opt.textContent = sub.name;
                    if (oldSubServiceId && sub.id == oldSubSubIdFallback(oldSubServiceId, sub.id)) {
                        opt.selected = true;
                    }
                    subServiceSelect.appendChild(opt);
                });
                subServiceSelect.disabled = false;
            })
            .catch(() => {
                subServiceSelect.disabled = false;
            });
    });

    function oldSubSubIdFallback(oldVal, currentVal) {
        return oldVal == currentVal ? currentVal : null;
    }

    // 3. عند تغيير المنطقة -> جلب المدن وتحديد الكل افتراضياً
    regionSelect.addEventListener('change', function () {
        const regionId = this.value;
        citiesContainer.innerHTML = '';
        currentCitiesList = [];
        updateCitiesCount();

        if (!regionId) {
            citiesPlaceholder.classList.remove('d-none');
            btnSelectAll.disabled = true;
            btnDeselectAll.disabled = true;
            citySearchInput.disabled = true;
            return;
        }

        citiesPlaceholder.classList.add('d-none');
        citiesLoading.classList.remove('d-none');
        btnSelectAll.disabled = true;
        btnDeselectAll.disabled = true;
        citySearchInput.disabled = true;

        fetch(`{{ url('admin/prices/ajax/region-cities') }}/${regionId}`)
            .then(res => res.json())
            .then(cities => {
                citiesLoading.classList.add('d-none');
                currentCitiesList = cities;
                renderCities(cities);
                btnSelectAll.disabled = false;
                btnDeselectAll.disabled = false;
                citySearchInput.disabled = false;
            })
            .catch(() => {
                citiesLoading.classList.add('d-none');
            });
    });

    // رندر بطاقات المدن: الكل محدد تلقائياً
    function renderCities(cities) {
        citiesContainer.innerHTML = '';
        if (!cities.length) {
            citiesContainer.innerHTML = '<span class="text-muted small p-2">{{ __('No cities in this region') }}</span>';
            updateCitiesCount();
            return;
        }

        cities.forEach(city => {
            const isSelected = oldCityIds.length > 0 
                ? oldCityIds.includes(String(city.id)) || oldCityIds.includes(Number(city.id))
                : true; // افتراضياً: محدد للكل!

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

            // عند النقر على البطاقة أو علامة (x): تبديل الحالة
            chip.addEventListener('click', function () {
                toggleCity(chip);
            });

            citiesContainer.appendChild(chip);
        });

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

    // زر تحديد الكل
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

    // زر إلغاء تحديد الكل
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

    // فلترة المدن بالبحث اللحظي
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

    const priceForm = document.getElementById('priceForm');
    priceForm.addEventListener('submit', function (e) {
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

    // تشغيل تلقائي في حال وجود قيم قديمة (old inputs)
    if (providerSelect.value) {
        providerSelect.dispatchEvent(new Event('change'));
    }
    if (regionSelect.value) {
        regionSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection
