{{-- الخدمات والكميات المشمولة --}}
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
            <button type="button" id="add-included" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="border-radius: 8px;">
                <i class="fa-solid fa-plus"></i>
                <span>{{ __('Add service') }}</span>
            </button>
        </div>

        <div id="included-rows">
            @foreach(old('services', $package->includedServices->toArray() ?? []) as $key => $included)
                <div class="p-3 rounded-3 mb-2 border bg-light-subtle included-row">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Service') }}</label>
                            <select name="services[{{ $key }}][service_id]" data-field="service_id" class="form-select" required style="border-radius: 8px;">
                                <option value="">{{ __('Choose service') }}</option>
                                @foreach($services as $service)
                                    @php
                                        $sName = $service->translations->firstWhere('locale', app()->getLocale())?->name
                                            ?? $service->translations->firstWhere('locale', 'ar')?->name
                                            ?? $service->name
                                            ?? $service->code;
                                    @endphp
                                    <option value="{{ $service->id }}" @selected(($included['service_id'] ?? null) == $service->id)>
                                        {{ $sName }} ({{ $service->code }}) · {{ number_format($service->base_price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Usage limit') }}</label>
                            <select name="services[{{ $key }}][is_unlimited]" data-field="is_unlimited" class="form-select limit-selector" style="border-radius: 8px;">
                                <option value="0" @selected(!($included['is_unlimited'] ?? false))>{{ __('Limited quantity') }}</option>
                                <option value="1" @selected($included['is_unlimited'] ?? false)>{{ __('Unlimited') }}</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Included quantity') }}</label>
                            <input name="services[{{ $key }}][included_quantity]" data-field="included_quantity" class="form-control quantity-input font-monospace" type="number" min="1" max="1000" value="{{ $included['included_quantity'] ?? '1' }}" style="border-radius: 8px;">
                        </div>
                        <div class="col-12 col-md-1 d-flex justify-content-end">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-row w-100" title="{{ __('Remove') }}" style="border-radius: 8px; height: 38px;">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <template id="included-template">
            <div class="p-3 rounded-3 mb-2 border bg-light-subtle included-row">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-semibold text-secondary">{{ __('Service') }}</label>
                        <select data-field="service_id" class="form-select" required style="border-radius: 8px;">
                            <option value="">{{ __('Choose service') }}</option>
                            @foreach($services as $service)
                                @php
                                    $sName = $service->translations->firstWhere('locale', app()->getLocale())?->name
                                        ?? $service->translations->firstWhere('locale', 'ar')?->name
                                        ?? $service->name
                                        ?? $service->code;
                                @endphp
                                <option value="{{ $service->id }}">
                                    {{ $sName }} ({{ $service->code }}) · {{ number_format($service->base_price, 2) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-secondary">{{ __('Usage limit') }}</label>
                        <select data-field="is_unlimited" class="form-select limit-selector" style="border-radius: 8px;">
                            <option value="0">{{ __('Limited quantity') }}</option>
                            <option value="1">{{ __('Unlimited') }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold text-secondary">{{ __('Included quantity') }}</label>
                        <input data-field="included_quantity" class="form-control quantity-input font-monospace" type="number" min="1" max="1000" value="1" style="border-radius: 8px;">
                    </div>
                    <div class="col-12 col-md-1 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-row w-100" title="{{ __('Remove') }}" style="border-radius: 8px; height: 38px;">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const rows = document.getElementById('included-rows');
    const template = document.getElementById('included-template');

    function syncLimits(row) {
        const selector = row.querySelector('.limit-selector');
        const quantity = row.querySelector('.quantity-input');
        if (selector && quantity) {
            const isUnlimited = selector.value === '1';
            quantity.disabled = isUnlimited;
            if (isUnlimited) {
                quantity.value = '';
            } else if (!quantity.value) {
                quantity.value = '1';
            }
        }
    }

    const rename = () => {
        rows.querySelectorAll('.included-row').forEach((row, i) => {
            row.querySelectorAll('[data-field]').forEach(field => {
                field.name = 'services[' + i + '][' + field.dataset.field + ']';
            });
            syncLimits(row);
        });
    };

    rows.addEventListener('click', e => {
        if (e.target.closest('.remove-row')) {
            e.target.closest('.included-row').remove();
            rename();
        }
    });

    rows.addEventListener('change', e => {
        if (e.target.classList.contains('limit-selector')) {
            syncLimits(e.target.closest('.included-row'));
        }
        rename();
    });

    document.getElementById('add-included')?.addEventListener('click', () => {
        rows.appendChild(template.content.cloneNode(true));
        rename();
    });

    rename();
})();
</script>
@endpush
