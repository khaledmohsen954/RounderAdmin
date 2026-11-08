@extends('layouts.admin')
@section('title', ($home->exists ? __('Edit home') : __('Add home')) . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <a href="{{ route('admin.realestate.index') }}" class="text-decoration-none text-secondary">
                    <i class="fa-solid fa-arrow-right me-1"></i>
                    {{ __('Homes') }}
                </a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $home->exists ? $home->name : __('Add home') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-0 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background: #fff3eb; color: var(--orange, #fb822f);">
                    <i class="fa-solid fa-building" style="font-size: 1rem;"></i>
                </span>
                <span>{{ $home->exists ? __('Edit home') : __('Add home') }}</span>
                @if($home->exists)
                    <span class="badge bg-light text-secondary border font-monospace fs-6">#{{ $home->id }}</span>
                @endif
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.realestate.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Form --}}
    <form method="post" action="{{ $home->exists ? route('admin.realestate.update', $home->id) : route('admin.realestate.store') }}" class="panel p-3 p-md-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f) !important; border-radius: 14px;">
        @csrf
        @if($home->exists) @method('PUT') @endif

        {{-- Basic Information Section --}}
        <div class="mb-4">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2 text-dark">
                <i class="fa-solid fa-circle-info text-rounder-orange"></i>
                <span>{{ __('Property Information') }}</span>
            </h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary" for="home-name">
                        <i class="fa-solid fa-building me-1 text-rounder-orange"></i>
                        {{ __('Home name') }} <span class="text-danger">*</span>
                    </label>
                    <input class="form-control" id="home-name" name="name" value="{{ old('name', $home->name) }}" required maxlength="120" placeholder="مثال: برج النخيل السكني" style="border-radius: 10px;">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold small text-secondary" for="home-property_code">
                        <i class="fa-solid fa-hashtag me-1 text-rounder-orange"></i>
                        {{ __('Property code') }} <span class="text-danger">*</span>
                    </label>
                    <input class="form-control font-monospace" id="home-property_code" name="property_code" value="{{ old('property_code', $home->property_code) }}" required maxlength="60" placeholder="e.g. HOM-104" style="border-radius: 10px;">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold small text-secondary" for="home-address">
                        <i class="fa-solid fa-location-dot me-1 text-rounder-orange"></i>
                        {{ __('Address') }} <span class="text-danger">*</span>
                    </label>
                    <input class="form-control" id="home-address" name="address" value="{{ old('address', $home->address) }}" required maxlength="255" placeholder="مثال: طريق الملك فهد، حي العليا، الرياض" style="border-radius: 10px;">
                </div>

                <div class="col-md-6">
                    <label for="home-city" class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-city me-1 text-rounder-orange"></i>
                        {{ __('City') }} <span class="text-danger">*</span>
                    </label>
                    <input id="city-search" type="search" class="form-control form-control-sm mb-2" placeholder="{{ __('Search Saudi cities') }}..." style="border-radius: 8px;">
                    <select id="home-city" name="city_id" class="form-select" required style="border-radius: 10px;">
                        <option value="">{{ __('Choose city') }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" @selected(old('city_id', $home->city_id) == $city->id)>
                                {{ $city->translations->firstWhere('locale', app()->getLocale())?->name ?? $city->code }} · {{ $city->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="floors_count" class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-layer-group me-1 text-rounder-orange"></i>
                        {{ __('Floors count') }} <span class="text-danger">*</span>
                    </label>
                    <input id="floors_count" name="floors_count" type="number" min="1" max="200" class="form-control" value="{{ old('floors_count', $home->floors_count ?? 1) }}" required style="border-radius: 10px;">
                </div>

                <div class="col-md-2">
                    <label for="health_percent" class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-heart-pulse me-1 text-rounder-orange"></i>
                        {{ __('Property health') }} (%) <span class="text-danger">*</span>
                    </label>
                    <input id="health_percent" name="health_percent" type="number" min="0" max="100" class="form-control" value="{{ old('health_percent', $home->health_percent ?? 100) }}" required style="border-radius: 10px;">
                </div>

                <div class="col-md-2">
                    <label for="association_balance" class="form-label fw-semibold small text-secondary">
                        <i class="fa-solid fa-coins me-1 text-rounder-orange"></i>
                        {{ __('Association balance') }} <span class="text-danger">*</span>
                    </label>
                    <input id="association_balance" name="association_balance" type="number" min="0" step=".01" class="form-control" value="{{ old('association_balance', $home->association_balance ?? 0) }}" required style="border-radius: 10px;">
                </div>
            </div>
        </div>

        {{-- Apartments Section --}}
        <div class="pt-4 border-top">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
                        <i class="fa-solid fa-door-open text-rounder-orange"></i>
                        <span>{{ __('Apartment details') }}</span>
                    </h5>
                    <p class="text-secondary small mb-0">{{ __('Floor numbers start at 1 and cannot exceed the building floors count.') }}</p>
                </div>
                <button type="button" id="add-unit" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                    <i class="fa-solid fa-plus"></i>
                    <span>{{ __('Add apartment') }}</span>
                </button>
            </div>

            <div class="table-responsive rounded-3 border" style="border-color: #e2ece7 !important;">
                <table class="table table-hover align-middle mb-0" style="min-width: 650px;">
                    <thead class="bg-light-subtle">
                        <tr class="text-secondary small fw-bold">
                            <th>{{ __('Apartment number') }} <span class="text-danger">*</span></th>
                            <th style="width: 140px;">{{ __('Floor') }} <span class="text-danger">*</span></th>
                            <th style="width: 180px;">{{ __('Area (m²)') }} <span class="text-danger">*</span></th>
                            <th style="width: 150px;">{{ __('Rooms count') }} <span class="text-danger">*</span></th>
                            <th class="text-end" style="width: 100px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody id="unit-rows">
                        @foreach(old('units', $home->exists && $home->units->isNotEmpty() ? $home->units->toArray() : [['unit_number' => '', 'floor_number' => 1, 'area_sqm' => '', 'rooms_count' => 1]]) as $i => $unit)
                            <tr>
                                <td>
                                    <input type="hidden" data-field="id" name="units[{{ $i }}][id]" value="{{ $unit['id'] ?? '' }}">
                                    <input aria-label="{{ __('Apartment number') }}" class="form-control form-control-sm" data-field="unit_number" name="units[{{ $i }}][unit_number]" value="{{ $unit['unit_number'] }}" maxlength="40" required placeholder="مثال: 101" style="border-radius: 8px;">
                                </td>
                                <td>
                                    <input aria-label="{{ __('Floor') }}" class="form-control form-control-sm" type="number" data-field="floor_number" name="units[{{ $i }}][floor_number]" value="{{ $unit['floor_number'] }}" min="1" max="200" required style="border-radius: 8px;">
                                </td>
                                <td>
                                    <input aria-label="{{ __('Area (m²)') }}" class="form-control form-control-sm" type="number" data-field="area_sqm" name="units[{{ $i }}][area_sqm]" value="{{ $unit['area_sqm'] }}" min=".01" max="99999999.99" step=".01" required placeholder="120.00" style="border-radius: 8px;">
                                </td>
                                <td>
                                    <input aria-label="{{ __('Rooms count') }}" class="form-control form-control-sm" type="number" data-field="rooms_count" name="units[{{ $i }}][rooms_count]" value="{{ $unit['rooms_count'] }}" min="1" max="100" required style="border-radius: 8px;">
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-unit d-inline-flex align-items-center justify-content-center" style="border-radius: 8px; width: 32px; height: 32px;" title="{{ __('Remove') }}">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit Buttons --}}
        <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
            <a href="{{ route('admin.realestate.index') }}" class="btn btn-light px-4" style="border-radius: 10px;">
                {{ __('Cancel') }}
            </a>
            <button class="btn btn-brand px-4" style="border-radius: 10px;">
                <i class="fa-solid fa-check me-1"></i>
                {{ __('Save') }}
            </button>
        </div>
    </form>

    {{-- Remove Unit Modal --}}
    <div class="modal fade" id="remove-unit-confirmation" tabindex="-1" aria-labelledby="remove-unit-title">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="remove-unit-title">{{ __('Confirm deletion') }}</h5>
                    <button type="button" class="btn-close ms-0 me-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3 text-secondary">
                    {{ __('Are you sure you want to delete this record?') }}
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light px-3" data-bs-dismiss="modal" style="border-radius: 10px;">{{ __('No') }}</button>
                    <button type="button" class="btn btn-danger px-4" id="remove-unit-yes" style="border-radius: 10px;">{{ __('Yes') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (() => {
        const rows = document.getElementById('unit-rows');
        const template = rows.firstElementChild.cloneNode(true);
        const renumber = () => [...rows.children].forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach(input => {
                input.name = 'units[' + index + '][' + input.dataset.field + ']';
            });
        });

        document.getElementById('add-unit').addEventListener('click', () => {
            if (rows.children.length >= 500) return;
            const row = template.cloneNode(true);
            row.querySelectorAll('input').forEach(input => {
                if (input.dataset.field === 'id') {
                    input.value = '';
                } else if (['floor_number', 'rooms_count'].includes(input.dataset.field)) {
                    input.value = '1';
                } else {
                    input.value = '';
                }
            });
            rows.append(row);
            renumber();
        });

        let pending = null;
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('remove-unit-confirmation'));
        rows.addEventListener('click', event => {
            const btn = event.target.closest('.remove-unit');
            if (!btn || rows.children.length <= 1) return;
            pending = btn.closest('tr');
            modal.show();
        });

        document.getElementById('remove-unit-yes').addEventListener('click', () => {
            if (pending) {
                pending.remove();
                pending = null;
                renumber();
            }
            modal.hide();
        });

        document.getElementById('city-search').addEventListener('input', function () {
            const query = this.value.trim().toLocaleLowerCase();
            for (const option of document.getElementById('home-city').options) {
                option.hidden = option.value !== '' && !option.text.toLocaleLowerCase().includes(query);
            }
        });
    })();
    </script>
    @endpush
</main>
@endsection
