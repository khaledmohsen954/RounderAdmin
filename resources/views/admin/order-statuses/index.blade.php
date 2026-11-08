@extends('layouts.admin')
@section('title', 'حالات الطلبات والطوارئ')
@section('body')

<style>
    .status-page-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }
    .status-section-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
        padding: 1.1rem 1.5rem;
    }
    .status-badge-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        padding: 0.3rem 0.65rem;
        border-radius: 8px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        display: inline-block;
    }
    .status-color-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.35rem 0.85rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.85rem;
    }
    .status-pill-pending, .status-pill-received { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
    .status-pill-confirmed, .status-pill-assigned { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .status-pill-in_progress, .status-pill-on_the_way { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
    .status-pill-completed, .status-pill-resolved { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .status-pill-cancelled { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

    .provider-visibility-label {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.45rem 0.9rem;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .provider-visibility-label:hover {
        border-color: #cbd5e1;
        background: #f1f5f9;
    }
    .provider-visibility-label.is-checked {
        background: #ecfdf5;
        border-color: #a7f3d0;
        color: #065f46;
    }
    .provider-visibility-label.is-unchecked {
        background: #fff1f2;
        border-color: #fecdd3;
        color: #9f1239;
    }
    .provider-visibility-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        accent-color: #0f4c5c;
    }
    .toast-save-indicator {
        position: fixed;
        bottom: 24px;
        left: 24px;
        z-index: 1080;
        background: #0f172a;
        color: #fff;
        padding: 0.75rem 1.25rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.18);
        display: none;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>

@php
    $canUpdate = app(\App\Modules\Access\Services\AdminPermissionService::class)
        ->allowsRoute(auth()->user(), 'admin.order-statuses.update', 'PUT');
@endphp

<main class="container py-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge rounded-pill" style="background: rgba(15, 76, 92, 0.1); color: #0f4c5c; font-weight: 700;">
                <i class="fa-solid fa-sliders me-1"></i> إعدادات العرض لمزود الخدمة
            </span>
        </div>
        <h1 class="h3 fw-bold mb-1 text-dark">حالات الطلبات والطوارئ</h1>
        <p class="text-secondary mb-0 small">
            استعراض جميع الحالات المعتمدة في النظام والتحكم في إظهار أو إخفاء كل حالة في بوابة <strong>مزود الخدمة</strong>.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.service-orders.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="fa-solid fa-clipboard-list me-1"></i> طلبات الصيانة
        </a>
        <a href="{{ route('admin.emergencies.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="fa-solid fa-truck-medical me-1"></i> طلبات الطوارئ
        </a>
    </div>
</div>

@include('admin.partials.feedback')

<form method="POST" action="{{ route('admin.order-statuses.update') }}" id="statusesVisibilityForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- 1. حالات طلبات الصيانة (BookingStatus) --}}
        <div class="col-12">
            <div class="status-page-card">
                <div class="status-section-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#0f4c5c;color:#fff;">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">حالات طلبات الصيانة والخدمات ({{ $bookingStatuses->count() }})</h2>
                            <span class="text-secondary" style="font-size: 0.78rem;">الحالات الأساسية لدورة حياة طلب الصيانة (Booking Statuses)</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 70px;">#</th>
                                <th>اسم الحالة (عربي)</th>
                                <th>اسم الحالة (إنجليزي)</th>
                                <th>الكود البرمجي</th>
                                <th class="text-center">عدد الطلبات المرتبطة</th>
                                <th class="text-center pe-4" style="width: 280px;">تظهر في يوزر مزود الخدمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookingStatuses as $index => $status)
                                @php
                                    $arName = $status->translations->firstWhere('locale', 'ar')?->name ?? $status->code;
                                    $enName = $status->translations->firstWhere('locale', 'en')?->name ?? $status->code;
                                    $isChecked = (bool) $status->show_in_provider_portal;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="status-color-pill status-pill-{{ $status->code }}">
                                            <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
                                            {{ $arName }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-secondary">{{ $enName }}</td>
                                    <td><span class="status-badge-code">{{ $status->code }}</span></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
                                            {{ number_format((int) $status->bookings_count) }} طلب
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <label class="provider-visibility-label {{ $isChecked ? 'is-checked' : 'is-unchecked' }}" id="label-booking-{{ $status->id }}">
                                            <input
                                                type="checkbox"
                                                class="provider-visibility-checkbox js-status-toggle"
                                                name="booking_statuses[]"
                                                value="{{ $status->id }}"
                                                data-type="booking"
                                                data-id="{{ $status->id }}"
                                                data-label-id="label-booking-{{ $status->id }}"
                                                data-text-id="text-booking-{{ $status->id }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ !$canUpdate ? 'disabled' : '' }}
                                            >
                                            <span class="fw-bold small" id="text-booking-{{ $status->id }}">
                                                {{ $isChecked ? 'تظهر لمزود الخدمة' : 'مخفية عن مزود الخدمة' }}
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 2. حالات طلبات الطوارئ (EmergencyRequestStatus) --}}
        <div class="col-12">
            <div class="status-page-card">
                <div class="status-section-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3" style="width:38px;height:38px;background:#dc2626;color:#fff;">
                            <i class="fa-solid fa-truck-medical"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">حالات بلاغات وطلبات الطوارئ ({{ $emergencyStatuses->count() }})</h2>
                            <span class="text-secondary" style="font-size: 0.78rem;">الحالات الأساسية لدورة حياة طلب الطوارئ (Emergency Statuses)</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 70px;">#</th>
                                <th>اسم الحالة (عربي)</th>
                                <th>اسم الحالة (إنجليزي)</th>
                                <th>الكود البرمجي</th>
                                <th class="text-center">عدد البلاغات المرتبطة</th>
                                <th class="text-center pe-4" style="width: 280px;">تظهر في يوزر مزود الخدمة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emergencyStatuses as $index => $status)
                                @php
                                    $arName = $status->translations->firstWhere('locale', 'ar')?->name ?? $status->code;
                                    $enName = $status->translations->firstWhere('locale', 'en')?->name ?? $status->code;
                                    $isChecked = (bool) $status->show_in_provider_portal;
                                @endphp
                                <tr>
                                    <td class="ps-4 fw-bold text-secondary">{{ $index + 1 }}</td>
                                    <td>
                                        <span class="status-color-pill status-pill-{{ $status->code }}">
                                            <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
                                            {{ $arName }}
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-secondary">{{ $enName }}</td>
                                    <td><span class="status-badge-code">{{ $status->code }}</span></td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill bg-light text-dark border px-3 py-1">
                                            {{ number_format((int) $status->emergency_requests_count) }} بلاغ
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <label class="provider-visibility-label {{ $isChecked ? 'is-checked' : 'is-unchecked' }}" id="label-emergency-{{ $status->id }}">
                                            <input
                                                type="checkbox"
                                                class="provider-visibility-checkbox js-status-toggle"
                                                name="emergency_statuses[]"
                                                value="{{ $status->id }}"
                                                data-type="emergency"
                                                data-id="{{ $status->id }}"
                                                data-label-id="label-emergency-{{ $status->id }}"
                                                data-text-id="text-emergency-{{ $status->id }}"
                                                {{ $isChecked ? 'checked' : '' }}
                                                {{ !$canUpdate ? 'disabled' : '' }}
                                            >
                                            <span class="fw-bold small" id="text-emergency-{{ $status->id }}">
                                                {{ $isChecked ? 'تظهر لمزود الخدمة' : 'مخفية عن مزود الخدمة' }}
                                            </span>
                                        </label>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($canUpdate)
        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary px-4 py-2 rounded-3 fw-bold">
                <i class="fa-solid fa-floppy-disk me-2"></i> حفظ إعدادات ظهور الحالات
            </button>
        </div>
    @endif
</form>
</main>

<div id="statusToast" class="toast-save-indicator">
    <i class="fa-solid fa-circle-check text-success"></i>
    <span id="statusToastText">تم حفظ التعديل بنجاح</span>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('.js-status-toggle');
    const toast = document.getElementById('statusToast');
    const toastText = document.getElementById('statusToastText');
    let toastTimer = null;

    function showToast(msg) {
        if (!toast) return;
        toastText.textContent = msg;
        toast.style.display = 'inline-flex';
        if (toastTimer) clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.style.display = 'none';
        }, 2400);
    }

    toggles.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const isChecked = checkbox.checked;
            const labelEl = document.getElementById(checkbox.dataset.labelId);
            const textEl = document.getElementById(checkbox.dataset.textId);

            if (labelEl) {
                labelEl.classList.toggle('is-checked', isChecked);
                labelEl.classList.toggle('is-unchecked', !isChecked);
            }
            if (textEl) {
                textEl.textContent = isChecked ? 'تظهر لمزود الخدمة' : 'مخفية عن مزود الخدمة';
            }

            fetch("{{ route('admin.order-statuses.update') }}", {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    type: checkbox.dataset.type,
                    id: parseInt(checkbox.dataset.id, 10),
                    show_in_provider_portal: isChecked
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.ok) {
                    showToast(data.message || 'تم تحديث حالة الظهور لمزود الخدمة.');
                }
            })
            .catch(() => {});
        });
    });
});
</script>
@endsection

