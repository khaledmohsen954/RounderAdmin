@extends('layouts.admin')
@section('title', ($emergency ? __('Create emergency request') : __('Create service order')) . ' · Rounder')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2.min.css') }}?v=4.1.0">
<link rel="stylesheet" href="{{ asset('assets/vendor/select2/select2-bootstrap-5-theme.rtl.min.css') }}?v=1.3.0">
<style>
.select2-container--bootstrap-5 .select2-selection {
    border-radius: 10px !important;
    min-height: 44px;
    padding: 6px 12px;
    border-color: #dee2e6;
}
.select2-container--bootstrap-5.select2-container--focus .select2-selection {
    border-color: var(--orange, #fb822f) !important;
    box-shadow: 0 0 0 0.25rem rgba(251, 130, 47, 0.2) !important;
}
.select2-container--bootstrap-5 .select2-dropdown {
    border-radius: 10px !important;
    border-color: #dee2e6;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}
.select2-container--bootstrap-5 .select2-dropdown .select2-search .select2-search__field {
    border-radius: 8px;
    padding: 8px 12px;
}
.select2-container--bootstrap-5 .select2-results__option--highlighted[aria-selected] {
    background-color: var(--petrol, #02333e) !important;
    color: #fff !important;
}
</style>
@endpush

@section('body')
<main class="container py-4">
    {{-- Top Header & Navigation --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a class="btn btn-sm btn-outline-secondary rounded-pill px-3" href="{{ route($emergency ? 'admin.emergencies.index' : 'admin.service-orders.index') }}">
                    <i class="fa-solid fa-arrow-right me-1"></i> {{ $emergency ? __('Emergency requests') : __('Service orders') }}
                </a>
                <span class="text-muted">/</span>
                <span class="text-secondary small fw-semibold">{{ $emergency ? __('Create emergency request') : __('Create service order') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2">
                @if($emergency)
                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                    <span>{{ __('Create emergency request') }}</span>
                @else
                    <i class="fa-solid fa-bell-concierge text-rounder-orange"></i>
                    <span>{{ __('Create service order') }}</span>
                @endif
            </h1>
            <p class="text-secondary mb-0 small">
                {{ $emergency ? __('Submit an urgent emergency request for immediate provider dispatch.') : __('Create and schedule a new service order for a resident unit.') }}
            </p>
        </div>
    </div>

    @include('admin.partials.feedback')

    @if($memberships->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ __('Add a resident linked to a property before creating a request.') }}</span>
        </div>
    @endif

    @if($options->isEmpty())
        <div class="alert alert-warning d-flex align-items-center gap-2 mb-3" style="border-radius: 10px;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>{{ __('No active request types are available.') }}</span>
        </div>
    @endif

    {{-- Main Panel Form --}}
    <div class="panel p-3 p-lg-4 shadow-sm" style="border-top: 3px solid {{ $emergency ? '#dc3545' : 'var(--orange, #fb822f)' }}; border-radius: 14px;">
        <form method="post" action="{{ route($emergency ? 'admin.emergencies.store' : 'admin.service-orders.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- 1. Resident & Property Details --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-gear text-rounder-orange"></i>
                    <span>{{ __('Resident and property details') }}</span>
                </h6>

                @php
                    $clients = $memberships->groupBy('user_id');
                    $selectedResidentHomeId = (int) old('resident_home_id', 0);
                    $initialSelectedUserId = 0;
                    if ($selectedResidentHomeId) {
                        $initialMembership = $memberships->firstWhere('id', $selectedResidentHomeId);
                        $initialSelectedUserId = $initialMembership?->user_id ?? 0;
                    }
                @endphp

                <div class="row g-3">
                    {{-- 1. اختيار العميل (الساكن) مع بحث Select2 حي --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="resident_user_id">
                            <i class="fa-solid fa-user text-primary me-1"></i> {{ __('Resident / Client') }} <span class="text-danger">*</span>
                        </label>
                        <select id="resident_user_id" class="form-select" required style="border-radius: 10px; width: 100%;">
                            <option value="">{{ __('Select resident') }}</option>
                            @foreach($clients as $userId => $userUnits)
                                @php $client = $userUnits->first(); @endphp
                                <option value="{{ $userId }}" data-phone="{{ $client->contact_phone ?? '' }}" @selected($initialSelectedUserId == $userId)>
                                    {{ $client->resident_name }}@if(!empty($client->contact_phone)) · {{ $client->contact_phone }}@endif
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i> {{ __('Search by resident name or phone number.') }}
                        </div>
                    </div>

                    {{-- 2. اختيار العقار والوحدة الخاصة بالعميل --}}
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="resident_home_id">
                            <i class="fa-solid fa-building-user text-rounder-orange me-1"></i> {{ __('Property and unit') }} <span class="text-danger">*</span>
                        </label>
                        <select id="resident_home_id" name="resident_home_id" class="form-select @error('resident_home_id') is-invalid @enderror" required style="border-radius: 10px; width: 100%;" disabled>
                            <option value="">{{ __('Select resident first') }}</option>
                        </select>
                        <div class="form-text small text-muted mt-1">
                            <i class="fa-solid fa-house-chimney text-primary me-1"></i> {{ __('Select the specific property and unit for the request.') }}
                        </div>
                        @error('resident_home_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted opacity-25">

            {{-- 2. Request Details --}}
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clipboard-list text-rounder-orange"></i>
                    <span>{{ __('Request details') }}</span>
                </h6>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-secondary" for="request_option">
                            <i class="fa-solid fa-wrench text-rounder-orange me-1"></i> {{ $emergency ? __('Emergency type') : __('Service') }} <span class="text-danger">*</span>
                        </label>
                        <select id="request_option" name="{{ $emergency ? 'emergency_type_id' : 'service_id' }}" class="form-select @error($emergency ? 'emergency_type_id' : 'service_id') is-invalid @enderror" required style="border-radius: 10px;">
                            <option value="">{{ __('Choose an option') }}</option>
                            @foreach($options as $option)
                                <option value="{{ $option->id }}" @selected(old($emergency ? 'emergency_type_id' : 'service_id') == $option->id)>
                                    {{ $option->name ?? $option->code }}
                                </option>
                            @endforeach
                        </select>
                        @error($emergency ? 'emergency_type_id' : 'service_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    @if($emergency)
                        <div class="col-12 col-md-6">
                            <label for="contact_phone" class="form-label small fw-semibold text-secondary">
                                <i class="fa-solid fa-phone text-rounder-orange me-1"></i> {{ __('Contact phone') }} <span class="text-danger">*</span>
                            </label>
                            <input id="contact_phone" name="contact_phone" type="tel" dir="ltr" value="{{ old('contact_phone') }}" class="form-control font-monospace @error('contact_phone') is-invalid @enderror" pattern="05[0-9]{8}" maxlength="10" placeholder="05XXXXXXXX" required style="border-radius: 10px;">
                            @error('contact_phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="location_details" class="form-label small fw-semibold text-secondary">
                                <i class="fa-solid fa-location-dot text-rounder-orange me-1"></i> {{ __('Location details') }} <span class="text-danger">*</span>
                            </label>
                            <input id="location_details" name="location_details" value="{{ old('location_details') }}" class="form-control @error('location_details') is-invalid @enderror" minlength="4" maxlength="255" placeholder="{{ __('e.g. Building A, Floor 2, Apartment 204') }}" required style="border-radius: 10px;">
                            @error('location_details')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-comment-dots text-rounder-orange me-1"></i> {{ __('Emergency description') }} <span class="text-danger">*</span>
                            </label>
                            <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="4" minlength="10" maxlength="3000" placeholder="{{ __('Describe the emergency situation in detail...') }}" required style="border-radius: 10px;">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check p-3 bg-danger-subtle rounded-3 border border-danger-subtle">
                                <input id="confirm_emergency" type="checkbox" name="confirm_emergency" value="1" class="form-check-input me-2" @checked(old('confirm_emergency')) required>
                                <label for="confirm_emergency" class="form-check-label text-danger fw-semibold">
                                    {{ __('I confirm this is an emergency request.') }}
                                </label>
                            </div>
                            @error('confirm_emergency')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @else
                        <div class="col-12 col-md-6" id="sub-service-field" hidden>
                            <label class="form-label small fw-semibold text-secondary" for="sub_service_id">
                                <i class="fa-solid fa-screwdriver-wrench text-rounder-orange me-1"></i> {{ __('Sub service') }} <span class="text-danger">*</span>
                            </label>
                            <select id="sub_service_id" name="sub_service_id" class="form-select @error('sub_service_id') is-invalid @enderror" disabled style="border-radius: 10px;">
                                <option value="">{{ __('Choose a sub service') }}</option>
                                @foreach($subServices as $subService)
                                    @php
                                        $subServiceName = $subService->translations->firstWhere('locale', app()->getLocale())?->name
                                            ?? $subService->translations->firstWhere('locale', 'ar')?->name
                                            ?? $subService->code;
                                    @endphp
                                    <option value="{{ $subService->id }}" data-service-id="{{ $subService->service_id }}" @selected(old('sub_service_id') == $subService->id)>
                                        {{ $subServiceName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('sub_service_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="scheduled_at" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-calendar-check text-rounder-orange me-1"></i> {{ __('Scheduled date and time') }} <span class="text-danger">*</span>
                            </label>
                            <input id="scheduled_at" name="scheduled_at" type="datetime-local" class="form-control font-monospace @error('scheduled_at') is-invalid @enderror" value="{{ old('scheduled_at') }}" required style="border-radius: 10px;">
                            @error('scheduled_at')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="notes" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-note-sticky text-rounder-orange me-1"></i> {{ __('Notes') }}
                            </label>
                            <textarea id="notes" name="notes" class="form-control @error('notes') is-invalid @enderror" maxlength="2000" rows="3" placeholder="{{ __('Add any special instructions or resident notes...') }}" style="border-radius: 10px;">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="images" class="form-label small fw-semibold text-secondary">
                                <i class="fa-regular fa-images text-rounder-orange me-1"></i> {{ __('Photos / Attachments') }}
                            </label>
                            <input id="images" name="images[]" type="file" class="form-control @error('images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp,image/jpg" multiple style="border-radius: 10px;">
                            <div class="form-text small text-muted">{{ __('You can attach photos to describe the problem or requested service.') }}</div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light-subtle rounded-3 border d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-info text-primary"></i>
                                <span class="small text-secondary">{{ __('The service price is taken from the current service catalogue.') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="d-flex justify-content-end gap-2 pt-4 border-top">
                <a class="btn btn-outline-secondary px-4" href="{{ route($emergency ? 'admin.emergencies.index' : 'admin.service-orders.index') }}" style="border-radius: 10px;">
                    {{ __('Cancel') }}
                </a>
                <button class="btn {{ $emergency ? 'btn-danger' : 'btn-brand' }} px-4 py-2 d-inline-flex align-items-center gap-2 shadow-sm" type="submit" @disabled($memberships->isEmpty() || $options->isEmpty()) style="border-radius: 10px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ $emergency ? __('Create emergency request') : __('Create request') }}</span>
                </button>
            </div>
        </form>
    </div>
</main>
@endsection

@push('scripts')
<script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('assets/vendor/select2/select2.min.js') }}"></script>
<script>
$(document).ready(function () {
    const $clientSelect = $('#resident_user_id');
    const $homeSelect = $('#resident_home_id');
    const $serviceSelect = $('#request_option');
    const $subServiceField = $('#sub-service-field');
    const $subServiceSelect = $('#sub_service_id');
    const phoneInput = document.getElementById('contact_phone');
    const isRtl = $('html').attr('dir') === 'rtl';

    $clientSelect.select2({
        theme: 'bootstrap-5',
        dir: isRtl ? 'rtl' : 'ltr',
        placeholder: "{{ __('Select resident') }}",
        allowClear: true,
        width: '100%',
        language: {
            noResults: function () {
                return "{{ __('No results found') }}";
            },
            searching: function () {
                return "{{ __('Searching...') }}";
            }
        }
    });

    const membershipsMap = @json($clients);
    const oldResidentHomeId = "{{ old('resident_home_id', $selectedResidentHomeId ?? '') }}";

    function updateSubServices() {
        const selectedServiceId = $serviceSelect.val();
        let hasSubServices = false;

        $subServiceSelect.find('option').each(function () {
            if (!this.value) return;
            const matchesService = this.dataset.serviceId === selectedServiceId;
            this.hidden = !matchesService;
            if (matchesService) {
                hasSubServices = true;
            } else if (this.selected) {
                this.selected = false;
            }
        });

        $subServiceField.prop('hidden', !hasSubServices);
        $subServiceSelect.prop('disabled', !hasSubServices).prop('required', hasSubServices);
        if (!hasSubServices) {
            $subServiceSelect.val('');
        }
    }

    function updateUnitsForClient(userId, preSelectedHomeId) {
        $homeSelect.empty();

        if (!userId || !membershipsMap[userId] || membershipsMap[userId].length === 0) {
            $homeSelect.append(new Option("{{ __('Select resident first') }}", ""));
            $homeSelect.prop('disabled', true);
            return;
        }

        const units = membershipsMap[userId];
        $homeSelect.append(new Option("{{ __('Select property and unit') }}", ""));

        let autoSelectedPhone = null;
        units.forEach(function (unit) {
            const label = unit.home_name + ' (' + unit.unit_label + ')';
            const isSelected = String(unit.id) === String(preSelectedHomeId) || (units.length === 1 && !preSelectedHomeId);
            const opt = new Option(label, unit.id, false, isSelected);
            opt.setAttribute('data-phone', unit.contact_phone || '');
            $homeSelect.append(opt);

            if (isSelected) {
                autoSelectedPhone = unit.contact_phone;
            }
        });

        $homeSelect.prop('disabled', false);

        // Update phone input with client contact phone
        const selectedOpt = $homeSelect.find('option:selected')[0];
        const phone = (selectedOpt && selectedOpt.getAttribute('data-phone')) || autoSelectedPhone || (units[0] && units[0].contact_phone);
        if (phone && phoneInput) {
            phoneInput.value = phone;
        }
    }

    $clientSelect.on('change', function () {
        const userId = this.value;
        updateUnitsForClient(userId, null);
    });
    $serviceSelect.on('change', updateSubServices);

    $homeSelect.on('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt && phoneInput) {
            const phone = selectedOpt.getAttribute('data-phone');
            if (phone) {
                phoneInput.value = phone;
            }
        }
    });

    // If client is already selected (e.g. from old input after validation error)
    if ($clientSelect.val()) {
        updateUnitsForClient($clientSelect.val(), oldResidentHomeId);
    }
    updateSubServices();
});
</script>
@endpush
