@extends('layouts.admin')
@section('title', __('Create customer package') . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route('admin.packages.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ __('Customer packages') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ __('Create customer package') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-rounder-orange"></i>
                <span>{{ __('Create customer package') }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Define a new subscription package, its billing cycles, pricing, features, and included services.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid var(--orange, #fb822f); border-radius: 14px;">
        @adminCan('admin.packages.store', 'POST')
        <form method="post" enctype="multipart/form-data" action="{{ route('admin.packages.store') }}" id="package-form">
            @csrf

            {{-- 1. Basic Settings --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                    <span>{{ __('Basic settings') }}</span>
                </h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Package code') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-barcode"></i>
                            </span>
                            <input name="code" value="{{ old('code') }}" class="form-control border-start-0 font-monospace @error('code') is-invalid @enderror" required maxlength="60" pattern="[A-Za-z0-9_-]+" placeholder="{{ __('e.g. basic-monthly, vip-yearly') }}" style="border-radius: 10px 0 0 10px;">
                            @error('code')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="fa-solid fa-calendar-check text-rounder-orange me-1"></i> {{ __('Billing cycle') }} <span class="text-danger">*</span>
                        </label>
                        <select name="billing_cycle_id" class="form-select @error('billing_cycle_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Choose billing cycle') }}</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" @selected(old('billing_cycle_id') == $cycle->id)>
                                    {{ __($cycle->code === 'semiannual' ? 'Semiannual' : ucfirst($cycle->code)) }} · {{ $cycle->months }} {{ __('months') }}
                                </option>
                            @endforeach
                        </select>
                        @error('billing_cycle_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Customer price before discount') }} <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                                <i class="fa-solid fa-money-bill-wave"></i>
                            </span>
                            <input name="price" value="{{ old('price') }}" class="form-control border-start-0 font-monospace @error('price') is-invalid @enderror" type="number" step=".01" min="0" required placeholder="0.00" style="border-radius: 10px 0 0 10px;">
                        </div>
                        @error('price')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Currency') }} <span class="text-danger">*</span>
                        </label>
                        <input name="currency_code" value="{{ old('currency_code', 'SAR') }}" class="form-control font-monospace text-center fw-bold @error('currency_code') is-invalid @enderror" required pattern="[A-Z]{3}" maxlength="3" style="border-radius: 10px;">
                        @error('currency_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-secondary">
                            {{ __('Duration months') }} <span class="text-danger">*</span>
                        </label>
                        <input name="duration_months" value="{{ old('duration_months', 1) }}" class="form-control font-monospace text-center @error('duration_months') is-invalid @enderror" type="number" min="1" max="120" required style="border-radius: 10px;">
                        @error('duration_months')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Pricing and Discount Fields --}}
                    @include('admin.packages.partials.pricing', ['item' => null, 'priceField' => 'price'])
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Multilingual Names & Descriptions --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-language text-rounder-orange"></i>
                    <span>{{ __('Multilingual names and descriptions') }}</span>
                </h6>
                <div class="row g-3">
                    @php
                        $packageLangs = [
                            'ar' => ['label' => 'العربية', 'flag' => '🇸🇦', 'placeholder' => 'مثال: باقة البريميوم الذهبية', 'desc_placeholder' => 'اكتب نبذة ومزايا الباقة بالتفصيل...', 'dir' => 'rtl'],
                            'en' => ['label' => 'English', 'flag' => '🇬🇧', 'placeholder' => 'e.g. Premium Gold Package', 'desc_placeholder' => 'Enter package description and key features...', 'dir' => 'ltr'],
                            'ur' => ['label' => 'اردو', 'flag' => '🇵🇰', 'placeholder' => 'مثال: پریمیم گولڈ پیکیج', 'desc_placeholder' => 'پیکیج کی تفصیلات درج کریں...', 'dir' => 'rtl'],
                        ];
                    @endphp

                    @foreach($packageLangs as $locale => $meta)
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-secondary">
                                <span class="me-1">{{ $meta['flag'] }}</span> {{ __('Package name') }} · {{ $meta['label'] }} <span class="text-danger">*</span>
                            </label>
                            <input name="name_{{ $locale }}"
                                   value="{{ old('name_'.$locale) }}"
                                   class="form-control mb-2 @error('name_'.$locale) is-invalid @enderror"
                                   required
                                   maxlength="140"
                                   placeholder="{{ $meta['placeholder'] }}"
                                   dir="{{ $meta['dir'] }}"
                                   style="border-radius: 10px;">
                            @error('name_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror

                            <label class="form-label small fw-semibold text-secondary mt-1">
                                {{ __('Package details') }} · {{ $meta['label'] }}
                            </label>
                            <textarea name="description_{{ $locale }}"
                                      rows="3"
                                      maxlength="5000"
                                      class="form-control @error('description_'.$locale) is-invalid @enderror"
                                      placeholder="{{ $meta['desc_placeholder'] }}"
                                      dir="{{ $meta['dir'] }}"
                                      style="border-radius: 10px;">{{ old('description_'.$locale) }}</textarea>
                            @error('description_'.$locale)
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 3. Comparison Values --}}
            @include('admin.packages.partials.comparison-values', ['featureValues' => collect()])

            {{-- 4. Included Services --}}
            <div class="col-12 mt-4">
                <div class="p-3 p-lg-4 rounded-3 border bg-white shadow-sm">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-wrench text-rounder-orange"></i>
                                <span>{{ __('Included services and quantities') }}</span>
                            </h6>
                            <p class="text-secondary small mb-0">{{ __('Define the free or included services and quantities granted to the resident in this package.') }}</p>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" id="add-service-row" style="border-radius: 8px;">
                            <i class="fa-solid fa-plus"></i>
                            <span>{{ __('Add service') }}</span>
                        </button>
                    </div>

                    <div id="package-service-rows"></div>

                    <template id="service-row-template">
                        <div class="p-3 rounded-3 mb-2 border bg-light-subtle package-service-row">
                            <div class="row g-2 align-items-end">
                                <div class="col-12 col-md-5">
                                    <label class="form-label small fw-semibold text-secondary">{{ __('Service') }}</label>
                                    <select data-name="service_id" class="form-select" required style="border-radius: 8px;">
                                        <option value="">{{ __('Choose service') }}</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">
                                                {{ $service->name ?? $service->code }} · {{ number_format($service->base_price, 2) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold text-secondary">{{ __('Usage limit') }}</label>
                                    <select data-name="is_unlimited" class="form-select limit-select" style="border-radius: 8px;">
                                        <option value="0">{{ __('Limited quantity') }}</option>
                                        <option value="1">{{ __('Unlimited') }}</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-semibold text-secondary">{{ __('Included quantity') }}</label>
                                    <input data-name="included_quantity" type="number" min="1" max="1000" class="form-control quantity-input font-monospace" value="1" style="border-radius: 8px;">
                                </div>
                                <div class="col-12 col-md-1 d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-service-row w-100" title="{{ __('Remove') }}" style="border-radius: 8px; height: 38px;">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-4 mt-4 border-top">
                <a href="{{ route('admin.packages.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn btn-brand px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save package') }}</span>
                </button>
            </div>
        </form>
        @endadminCan
    </div>
</main>
@endsection

@push('scripts')
<script>
(() => {
    const container = document.getElementById('package-service-rows');
    const template = document.getElementById('service-row-template');
    let rowIndex = 0;

    function addRow() {
        if (!container || !template) return;
        const node = template.content.cloneNode(true);
        const row = node.querySelector('.package-service-row');
        row.querySelectorAll('[data-name]').forEach((field) => {
            field.name = 'services[' + rowIndex + '][' + field.dataset.name + ']';
        });

        const limitSelect = row.querySelector('.limit-select');
        const quantityInput = row.querySelector('.quantity-input');

        limitSelect.addEventListener('change', (event) => {
            const isUnlimited = event.target.value === '1';
            quantityInput.disabled = isUnlimited;
            if (isUnlimited) {
                quantityInput.value = '';
            } else if (!quantityInput.value) {
                quantityInput.value = '1';
            }
        });

        row.querySelector('.remove-service-row').addEventListener('click', () => row.remove());
        container.appendChild(row);
        rowIndex++;
    }

    document.getElementById('add-service-row')?.addEventListener('click', addRow);
    addRow();
})();
</script>
@endpush
