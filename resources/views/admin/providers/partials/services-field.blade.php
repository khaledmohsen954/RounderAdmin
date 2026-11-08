@php
    $selectedServiceIds = old('service_ids', isset($provider) ? $provider->services->modelKeys() : []);
    $selectedServiceIds = array_map('strval', (array) $selectedServiceIds);
@endphp
<div class="col-12" id="services-field-wrapper">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </span>
            <div>
                <h5 class="fw-bold mb-0 text-dark">{{ __('Assigned services') }}</h5>
                <div class="text-muted small">{{ __('Select services this provider is authorized to perform') }}</div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5" id="btn-select-all-services" style="border-radius: 8px; font-size: 0.78rem;">
                <i class="fa-solid fa-check-double me-1 text-rounder-orange"></i>{{ __('Select all') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5" id="btn-deselect-all-services" style="border-radius: 8px; font-size: 0.78rem;">
                <i class="fa-solid fa-xmark me-1 text-danger"></i>{{ __('Deselect all') }}
            </button>
            <span class="pill px-3 py-1 fw-bold" style="background: rgba(251, 130, 47, 0.12); color: var(--orange); border-color: rgba(251, 130, 47, 0.25); font-size: 0.82rem;">
                <i class="fa-solid fa-circle-check me-1"></i>
                <span id="services-counter">{{ count($selectedServiceIds) }}</span> {{ __('Selected') }}
            </span>
        </div>
    </div>

    <input type="hidden" name="_service_ids_submitted" value="1">

    <div class="row g-2.5">
        @foreach($services as $service)
            @php
                $serviceName = $service->translations->firstWhere('locale', app()->getLocale())?->name ?? $service->code;
                $isChecked = in_array((string) $service->id, $selectedServiceIds, true);
            @endphp
            <div class="col-12 col-sm-6 col-lg-4">
                <label class="service-checkbox-card w-100 {{ $isChecked ? 'is-selected' : '' }}">
                    <div class="d-flex align-items-center gap-2.5 text-truncate">
                        <span class="service-icon flex-shrink-0">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </span>
                        <span class="fw-bold text-dark fs-6 text-truncate" title="{{ $serviceName }}">{{ $serviceName }}</span>
                    </div>
                    <input class="form-check-input service-check-box flex-shrink-0" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                        @checked($isChecked)>
                </label>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(() => {
    const wrapper = document.getElementById('services-field-wrapper');
    if (!wrapper) return;
    const counter = document.getElementById('services-counter');
    const checkboxes = wrapper.querySelectorAll('.service-check-box');
    const updateCount = () => {
        let count = 0;
        checkboxes.forEach(cb => {
            const card = cb.closest('.service-checkbox-card');
            if (cb.checked) {
                count++;
                if (card) card.classList.add('is-selected');
            } else {
                if (card) card.classList.remove('is-selected');
            }
        });
        if (counter) counter.textContent = count;
    };
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));
    updateCount();

    const selectAllBtn = document.getElementById('btn-select-all-services');
    const deselectAllBtn = document.getElementById('btn-deselect-all-services');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => { cb.checked = true; });
            updateCount();
        });
    }
    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', () => {
            checkboxes.forEach(cb => { cb.checked = false; });
            updateCount();
        });
    }
})();
</script>
@endpush
