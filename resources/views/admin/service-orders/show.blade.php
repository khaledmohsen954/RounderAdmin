@extends('layouts.admin')
@section('title', __('Booking details').' #'.$booking->id.' · Rounder')
@section('body')
@php
    $locale = app()->getLocale();
    $serviceTranslation = $booking->service?->translations->firstWhere('locale', $locale);
    $serviceName = $serviceTranslation?->name ?? $booking->service?->code ?? '—';
    $categoryTranslation = $booking->service?->category?->translations->firstWhere('locale', $locale);
    $categoryName = $categoryTranslation?->name ?? $booking->service?->category?->code ?? null;
    $subServiceTranslation = $booking->subService?->translations->firstWhere('locale', $locale);
    $subServiceName = $subServiceTranslation?->name ?? $booking->subService?->code ?? null;
    $statusTranslation = $booking->status?->translations->firstWhere('locale', $locale);
    $statusName = $statusTranslation?->name ?? $booking->status?->code ?? '—';
    $statusCode = $booking->status?->code ?? 'pending';
    $cityTranslation = $booking->home?->city?->translations->firstWhere('locale', $locale);
    $cityName = $cityTranslation?->name ?? $booking->home?->city?->code ?? '—';
    $assignment = $booking->assignment;
    $providerProfile = $assignment?->providerProfile ?? $booking->serviceProvider;
    $commission = $booking->providerCommissionEntries->first();

    $statusHistory = $booking->bookingStatusHistory ?? collect();
    $statusMap = \App\Modules\Bookings\Models\BookingStatus::pluck('id', 'code');
    $submittedAt = $statusHistory->firstWhere('booking_status_id', $statusMap['pending'] ?? 6)?->created_at ?? $booking->created_at;
    $confirmedAt = $statusHistory->firstWhere('booking_status_id', $statusMap['confirmed'] ?? 7)?->created_at;
    $inProgressAt = $statusHistory->firstWhere('booking_status_id', $statusMap['in_progress'] ?? 8)?->created_at;
    $completedAt = $statusHistory->firstWhere('booking_status_id', $statusMap['completed'] ?? 9)?->created_at ?? $booking->completed_at;
    $cancelledAt = $statusHistory->firstWhere('booking_status_id', $statusMap['cancelled'] ?? 10)?->created_at;

    $isCancelled = $statusCode === 'cancelled';

    $sequenceCodes = ['pending', 'confirmed', 'in_progress', 'completed'];
    $sequenceIndex = array_search($statusCode, $sequenceCodes, true);
    if ($sequenceIndex === false) {
        $sequenceIndex = -1;
    }

    $stageTitles = [
        'pending' => __('Order Submitted'),
        'confirmed' => __('Order Accepted'),
        'in_progress' => __('Technician On The Way'),
        'completed' => __('Service Completed'),
    ];

    $stageIcons = [
        'pending' => 'fa-paper-plane',
        'confirmed' => 'fa-calendar-check',
        'in_progress' => 'fa-truck-fast',
        'completed' => 'fa-check-double',
    ];

    $stages = [];
    foreach ($sequenceCodes as $idx => $code) {
        $st = $statuses->firstWhere('code', $code);
        $stName = $stageTitles[$code] ?? ($st?->name ?? $code);
        $stId = $st?->id;

        $timestamp = match($code) {
            'pending' => $submittedAt,
            'confirmed' => $confirmedAt ?? ($assignment?->accepted_at ?? $assignment?->assigned_at),
            'in_progress' => $inProgressAt,
            'completed' => $completedAt,
            default => null,
        };

        $isCurrent = ($statusCode === $code);
        $isDone = ($statusCode === 'completed') ? true : ($sequenceIndex >= 0 && $idx < $sequenceIndex);

        $stages[] = [
            'id' => $stId,
            'code' => $code,
            'title' => $stName,
            'is_done' => $isDone,
            'is_current' => $isCurrent,
            'timestamp' => $timestamp,
            'icon' => $stageIcons[$code] ?? 'fa-circle-dot',
        ];
    }

    $cleanPhone = preg_replace('/[^0-9]/', '', (string)($booking->resident?->phone ?? ''));
    $waPhone = str_starts_with($cleanPhone, '05') ? ('966' . substr($cleanPhone, 1)) : $cleanPhone;
@endphp

<style>
    .place-items-center {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    .rounded-circle i {
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .order-stepper-line {
        position: absolute;
        top: 20px;
        inset-inline-start: 12%;
        inset-inline-end: 12%;
        height: 3px;
        background: #e5ede9;
        z-index: 1;
    }
    .order-stepper-progress {
        height: 100%;
        background: var(--orange);
        transition: width 0.3s ease;
    }

    /* Print Document Styling */
    .print-voucher {
        display: none !important;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }
        body {
            background: #fff !important;
            color: #000 !important;
            font-family: "Tajawal", system-ui, sans-serif !important;
            font-size: 10pt !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .web-only-view,
        .d-print-none,
        .topbar,
        .admin-sidebar,
        #admin-menu,
        button,
        .btn,
        form {
            display: none !important;
        }
        .print-voucher {
            display: block !important;
            width: 100% !important;
            color: #111 !important;
        }
        .print-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 12px !important;
        }
        .print-table th, .print-table td {
            border: 1px solid #d5dedb !important;
            padding: 6px 10px !important;
            font-size: 9pt !important;
            vertical-align: middle !important;
        }
        .print-table th {
            background-color: #f4f8f7 !important;
            color: #02333E !important;
            font-weight: 700 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .print-section-header {
            background-color: #edf5f3 !important;
            color: #02333E !important;
            font-weight: 800 !important;
            padding: 6px 10px !important;
            font-size: 9.5pt !important;
            border: 1px solid #c8dbd6 !important;
            margin-bottom: -1px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

{{-- ======================================================== --}}
{{-- 1. OFFICIAL PRINTABLE SERVICE ORDER VOUCHER (PRINT ONLY) --}}
{{-- ======================================================== --}}
<div class="print-voucher">
    {{-- Header with Logo, Title, and Order # --}}
    <div style="border-bottom: 2px solid #02333E; padding-bottom: 10px; margin-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 15pt; font-weight: 800; color: #02333E; margin: 0 0 2px 0;">
                    شركة راوندر لخدمات الصيانة وإدارة العقارات
                </h1>
                <div style="font-size: 8.5pt; color: #555;">
                    Rounder Property Management & Maintenance Services
                </div>
            </div>
            <div style="text-align: left;">
                <div style="font-size: 13pt; font-weight: 800; color: #FB822F;">
                    سند أمر خدمة (Service Order)
                </div>
                <div style="font-size: 10pt; font-weight: 700; color: #02333E;">
                    رقم الطلب: #{{ $booking->id }}
                </div>
                <div style="font-size: 8pt; color: #666;">
                    تاريخ الطباعة: {{ now()->format('Y-m-d H:i') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Vital Info Top Bar --}}
    <table class="print-table" style="margin-bottom: 12px;">
        <tr>
            <th style="width: 15%;">حالة الطلب:</th>
            <td style="width: 35%;">
                <strong>{{ $statusName }}</strong>
            </td>
            <th style="width: 15%;">موعد الحجز:</th>
            <td style="width: 35%;">
                <strong>{{ $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d H:i') : '—' }}</strong>
            </td>
        </tr>
        <tr>
            <th>تاريخ الإنشاء:</th>
            <td>{{ $booking->created_at ? $booking->created_at->format('Y-m-d H:i') : '—' }}</td>
            <th>إجمالي المبلغ:</th>
            <td><strong style="color: #02333E; font-size: 11pt;">{{ number_format($booking->total_amount, 2) }} SAR</strong></td>
        </tr>
    </table>

    {{-- Section 1: بيانات الخدمة --}}
    <div class="print-section-header">1. تفاصيل الخدمة ونطاق العمل</div>
    <table class="print-table">
        <tr>
            <th style="width: 20%;">الخدمة المطلوبة:</th>
            <td style="width: 30%;"><strong>{{ $serviceName }}</strong></td>
            <th style="width: 20%;">قسم الخدمة:</th>
            <td style="width: 30%;">{{ $categoryName ?? '—' }}</td>
        </tr>
        <tr>
            <th>الخدمة الفرعية:</th>
            <td>{{ $subServiceName ?? '—' }}</td>
            <th>السعر الأساسي:</th>
            <td>{{ number_format($booking->service?->base_price ?? $booking->total_amount, 2) }} SAR</td>
        </tr>
        @if($booking->notes)
            <tr>
                <th>ملاحظات وتوجيهات العميل:</th>
                <td colspan="3" style="font-style: italic; background-color: #fafafa;">"{{ $booking->notes }}"</td>
            </tr>
        @endif
    </table>

    {{-- Section 2: بيانات العميل والموقع --}}
    <div class="print-section-header">2. بيانات الساكن وموقع العقار</div>
    <table class="print-table">
        <tr>
            <th style="width: 20%;">اسم الساكن:</th>
            <td style="width: 30%;"><strong>{{ $booking->resident?->name ?? '—' }}</strong></td>
            <th style="width: 20%;">رقم الجوال:</th>
            <td style="width: 30%;" dir="ltr"><strong>{{ $booking->resident?->phone ?? '—' }}</strong></td>
        </tr>
        <tr>
            <th>اسم العقار:</th>
            <td>{{ $booking->home?->name ?? '—' }} ({{ $cityName }})</td>
            <th>رقم / اسم الوحدة:</th>
            <td><strong>{{ $booking->residentHome?->unit_label ?? '—' }}</strong></td>
        </tr>
        @if($booking->home?->address || $booking->resident?->email)
            <tr>
                <th>العنوان:</th>
                <td>{{ $booking->home?->address ?? '—' }}</td>
                <th>البريد الإلكتروني:</th>
                <td>{{ $booking->resident?->email ?? '—' }}</td>
            </tr>
        @endif
    </table>

    {{-- Section 3: بيانات مزود الخدمة ومسار التنفيذ --}}
    <div class="print-section-header">3. مزود الخدمة ومسار تنفيذ الطلب</div>
    <table class="print-table">
        <tr>
            <th style="width: 20%;">المزود المعين:</th>
            <td style="width: 30%;"><strong>{{ $providerProfile?->company_name ?? 'لم يعين بعد' }}</strong></td>
            <th style="width: 20%;">هاتف المزود:</th>
            <td style="width: 30%;" dir="ltr">{{ $providerProfile?->user?->phone ?? '—' }}</td>
        </tr>
    </table>

    {{-- Progress Steps Summary --}}
    <table class="print-table" style="text-align: center;">
        <thead>
            <tr>
                <th style="width: 25%; text-align: center;">المرحلة 1: تم الإرسال</th>
                <th style="width: 25%; text-align: center;">المرحلة 2: تم القبول</th>
                <th style="width: 25%; text-align: center;">المرحلة 3: الفني في الطريق</th>
                <th style="width: 25%; text-align: center;">المرحلة 4: اكتمال الخدمة</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $submittedAt ? \Carbon\Carbon::parse($submittedAt)->format('Y-m-d H:i') : '—' }}</td>
                <td>{{ $confirmedAt ? \Carbon\Carbon::parse($confirmedAt)->format('Y-m-d H:i') : '—' }}</td>
                <td>{{ $inProgressAt ? \Carbon\Carbon::parse($inProgressAt)->format('Y-m-d H:i') : '—' }}</td>
                <td>{{ $completedAt ? \Carbon\Carbon::parse($completedAt)->format('Y-m-d H:i') : '—' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Section 4: تقييم العميل (إن وجد) --}}
    @if($booking->bookingRatings->isNotEmpty())
        @php $r = $booking->bookingRatings->first(); @endphp
        <div class="print-section-header">4. تقييم ورأي العميل</div>
        <table class="print-table">
            <tr>
                <th style="width: 20%;">التقييم:</th>
                <td style="width: 30%;"><strong>{{ $r->score }} / 5 نجوم</strong></td>
                <th style="width: 20%;">الوسوم:</th>
                <td style="width: 30%;">{{ !empty($r->tags) ? implode(' · ', $r->tags) : '—' }}</td>
            </tr>
            @if($r->comment)
                <tr>
                    <th>ملاحظات التقييم:</th>
                    <td colspan="3" style="font-style: italic;">"{{ $r->comment }}"</td>
                </tr>
            @endif
        </table>
    @endif

    {{-- Section 5: التوقيعات والاعتماد --}}
    <div style="margin-top: 20px; padding-top: 10px; border-top: 1.5px dashed #aaa;">
        <table style="width: 100%; text-align: center; border: none;">
            <tr style="border: none;">
                <td style="width: 33.3%; border: none; padding: 5px;">
                    <div style="font-size: 8.5pt; font-weight: 700; color: #333; margin-bottom: 35px;">توقيع العميل / المستلم</div>
                    <div style="border-bottom: 1px dotted #888; width: 75%; margin: 0 auto;"></div>
                </td>
                <td style="width: 33.3%; border: none; padding: 5px;">
                    <div style="font-size: 8.5pt; font-weight: 700; color: #333; margin-bottom: 35px;">توقيع الفني / مقدم الخدمة</div>
                    <div style="border-bottom: 1px dotted #888; width: 75%; margin: 0 auto;"></div>
                </td>
                <td style="width: 33.3%; border: none; padding: 5px;">
                    <div style="font-size: 8.5pt; font-weight: 700; color: #333; margin-bottom: 35px;">ختم واعتماد الإدارة</div>
                    <div style="border-bottom: 1px dotted #888; width: 75%; margin: 0 auto;"></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Footer note --}}
    <div style="margin-top: 15px; text-align: center; font-size: 7.5pt; color: #888;">
        تم استخراج هذا السند إلكترونياً من منصة راوندر لإدارة العقارات والخدمات · جميع الحقوق محفوظة © {{ date('Y') }} Rounder
    </div>
</div>

{{-- ======================================================== --}}
{{-- 2. INTERACTIVE DASHBOARD VIEW (SCREEN ONLY - D-PRINT-NONE) --}}
{{-- ======================================================== --}}
<div class="web-only-view d-print-none container-fluid px-2 px-sm-3 px-lg-4 py-3 py-lg-4">
    <div class="row g-3">
        <main class="col-12 content">
            {{-- Breadcrumb & Quick Top Actions --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 bg-transparent p-0 align-items-center">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-house" style="font-size:0.8rem;"></i>
                                <span>{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.service-orders.index') }}" class="text-decoration-none text-secondary">
                                {{ __('Service orders') }}
                            </a>
                        </li>
                        <li class="breadcrumb-item active fw-bold text-dark" aria-current="page">
                            #{{ $booking->id }}
                        </li>
                    </ol>
                </nav>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary" title="{{ __('Print details') }}">
                        <i class="fa-solid fa-print me-1"></i> <span class="d-none d-sm-inline">{{ __('Print details') }}</span>
                    </button>
                    <a href="{{ route('admin.service-orders.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fa-solid {{ $locale === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i> {{ __('Back to service orders') }}
                    </a>
                    @adminCan('admin.service-orders.destroy', 'DELETE')
                        @include('admin.partials.delete-button', ['action' => route('admin.service-orders.destroy', $booking->id)])
                    @endadminCan
                </div>
            </div>

            @include('admin.partials.feedback')

            {{-- Unified Executive Order Header & Stepper --}}
            <div class="panel p-3 p-lg-4 mb-3 shadow-sm border-0" style="border-radius: 14px; background: #fff;">
                {{-- Top Summary Bar --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge px-3 py-2 fs-6 fw-bold text-white shadow-sm" style="background: var(--petrol); border-radius: 8px;">
                            #{{ $booking->id }}
                        </span>

                        @if($statusCode === 'completed')
                            <span class="badge bg-success px-3 py-2 fs-6 shadow-sm">
                                <i class="fa-solid fa-check-double me-1"></i> {{ $statusName }}
                            </span>
                        @elseif($statusCode === 'confirmed')
                            <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:#025968; color:#DDF3EE;">
                                <i class="fa-solid fa-calendar-check me-1"></i> {{ $statusName }}
                            </span>
                        @elseif($statusCode === 'in_progress')
                            <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:var(--orange); color:#fff;">
                                <i class="fa-solid fa-truck-fast me-1"></i> {{ $statusName }}
                            </span>
                        @elseif($statusCode === 'cancelled')
                            <span class="badge bg-danger px-3 py-2 fs-6 shadow-sm">
                                <i class="fa-solid fa-ban me-1"></i> {{ $statusName }}
                            </span>
                        @else
                            <span class="badge px-3 py-2 fs-6 shadow-sm" style="background:#fff3ea; color:#b6530b; border:1px solid #ffd8bf;">
                                <i class="fa-solid fa-hourglass-half me-1"></i> {{ $statusName }}
                            </span>
                        @endif

                        <h1 class="h5 fw-bold mb-0 text-dark ms-2">{{ $serviceName }}</h1>
                        @if($subServiceName)
                            <span class="badge bg-light text-secondary border">
                                <i class="fa-solid fa-check text-rounder-orange me-1"></i>{{ $subServiceName }}
                            </span>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <div class="text-start text-sm-end">
                            <div class="small text-muted">{{ __('Total price') }}</div>
                            <div class="h5 fw-bold text-dark mb-0">
                                {{ number_format($booking->total_amount, 2) }} <span class="fs-6 fw-normal text-muted">SAR</span>
                            </div>
                        </div>
                        <div class="vr mx-1 d-none d-sm-block" style="height: 32px;"></div>
                        <div class="text-start text-sm-end d-none d-sm-block">
                            <div class="small text-muted">{{ __('Scheduled date and time') }}</div>
                            <div class="fw-semibold text-dark mb-0" style="font-size: 0.9rem;">
                                <i class="fa-regular fa-clock text-warning me-1"></i>{{ $booking->scheduled_at ? $booking->scheduled_at->format('Y-m-d H:i') : '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compact 4-Step Progress Stepper --}}
                <div class="pt-3">
                    @if($isCancelled)
                        <div class="alert alert-danger d-flex align-items-center gap-2 mb-0 py-2" role="alert">
                            <i class="fa-solid fa-circle-exclamation fs-5"></i>
                            <div class="small">
                                <strong>{{ __('Order Cancelled') }}</strong>
                                @if($cancelledAt)
                                    <span class="ms-1">({{ \Carbon\Carbon::parse($cancelledAt)->format('Y-m-d H:i') }})</span>
                                @endif
                            </div>
                        </div>
                    @else
                        @php
                            $progressPercent = ($sequenceIndex >= 0) ? min(100, ($sequenceIndex / (count($sequenceCodes) - 1)) * 100) : 0;
                        @endphp
                        <div class="position-relative py-2">
                            {{-- Connecting Progress Line --}}
                            <div class="order-stepper-line d-none d-md-block">
                                <div class="order-stepper-progress" style="width: {{ $progressPercent }}%;"></div>
                            </div>

                            <div class="row g-2 position-relative" style="z-index: 2;">
                                @foreach($stages as $index => $stage)
                                    <div class="col-6 col-md-3">
                                        <div class="p-2 rounded-3 text-center h-100"
                                             style="background: {{ $stage['is_current'] ? '#fff7f0' : ($stage['is_done'] ? '#f4faf7' : '#fafafa') }};
                                                    border: 1px solid {{ $stage['is_current'] ? 'var(--orange)' : ($stage['is_done'] ? '#c2e5dc' : '#e5e7eb') }};">
                                            
                                            <div class="d-flex align-items-center justify-content-center gap-2 mb-1">
                                                @if($stage['is_current'])
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white flex-shrink-0"
                                                         style="width: 28px; height: 28px; background: var(--orange); box-shadow: 0 0 0 3px rgba(251,130,47,0.22); font-size: 0.8rem;">
                                                        <i class="fa-solid {{ $stage['icon'] }}"></i>
                                                    </div>
                                                @elseif($stage['is_done'])
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                                         style="width: 28px; height: 28px; background: var(--mint); color: var(--petrol); border: 1.5px solid #025968; font-size: 0.8rem;">
                                                        <i class="fa-solid fa-check"></i>
                                                    </div>
                                                @else
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-muted flex-shrink-0"
                                                         style="width: 28px; height: 28px; background: #fff; border: 1.5px dashed #cbd5e1; font-size: 0.75rem;">
                                                        <i class="fa-solid {{ $stage['icon'] }}"></i>
                                                    </div>
                                                @endif

                                                <div class="fw-bold text-dark text-truncate text-start" style="font-size: 0.82rem;">
                                                    {{ $stage['title'] }}
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center justify-content-between px-1" style="font-size: 0.7rem;">
                                                @if($stage['timestamp'] && ($stage['is_done'] || $stage['is_current']))
                                                    <span class="text-muted"><i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($stage['timestamp'])->format('H:i') }}</span>
                                                    <span class="badge {{ $stage['is_current'] ? 'bg-warning text-dark' : 'bg-success-subtle text-success' }}" style="font-size: 0.65rem;">
                                                        {{ $stage['is_current'] ? __('Current') : __('Done') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted fst-italic">{{ __('Pending') }}</span>
                                                    <span class="text-muted" style="font-size: 0.65rem;">{{ __('Stage') }} {{ $index + 1 }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Clean 2-Column Dashboard Body --}}
            <div class="row g-3">
                {{-- Main Column (8 cols): Unified Order, Customer Details & Photos --}}
                <div class="col-lg-8">
                    {{-- 2.1 Unified Booking, Service & Customer Details Card --}}
                    <div class="panel p-3 p-lg-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-clipboard-list text-primary"></i>
                                <span>{{ __('Booking details') }} & {{ __('Resident and home') }}</span>
                            </h2>
                            @if($booking->home?->property_code)
                                <span class="badge bg-light text-dark border">{{ $booking->home->property_code }}</span>
                            @endif
                        </div>

                        <div class="row g-4">
                            {{-- Service & Scope Side --}}
                            <div class="col-md-6 border-end-md">
                                <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing: 0.04em;">
                                    {{ __('Service and scope') }}
                                </div>
                                <div class="d-grid gap-2 small">
                                    <div class="row g-2 align-items-center py-1">
                                        <div class="col-5 text-secondary">{{ __('Available services') }}:</div>
                                        <div class="col-7 fw-semibold text-dark">{{ $serviceName }}</div>
                                    </div>
                                    @if($categoryName)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-5 text-secondary">{{ __('Service categories') }}:</div>
                                            <div class="col-7"><span class="pill py-0 px-2">{{ $categoryName }}</span></div>
                                        </div>
                                    @endif
                                    @if($subServiceName)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-5 text-secondary">{{ __('Sub service') }}:</div>
                                            <div class="col-7 text-primary fw-semibold">{{ $subServiceName }}</div>
                                        </div>
                                    @endif
                                    <div class="row g-2 align-items-center py-1">
                                        <div class="col-5 text-secondary">{{ __('Base price') }}:</div>
                                        <div class="col-7 text-dark">{{ number_format($booking->service?->base_price ?? $booking->total_amount, 2) }} SAR</div>
                                    </div>
                                    <div class="row g-2 align-items-center py-1">
                                        <div class="col-5 text-secondary">{{ __('Total amount') }}:</div>
                                        <div class="col-7"><strong class="text-dark">{{ number_format($booking->total_amount, 2) }} SAR</strong></div>
                                    </div>
                                </div>

                                {{-- Resident Instructions / Notes --}}
                                <div class="mt-3 pt-2 border-top">
                                    <div class="small fw-semibold text-secondary mb-1">
                                        <i class="fa-regular fa-comment-dots me-1 text-primary"></i>{{ __('Resident instructions') }}
                                    </div>
                                    @if($booking->notes)
                                        <div class="p-2 rounded-2 small" style="background:var(--sand); border-inline-start:3px solid var(--orange);">
                                            <div class="fst-italic text-dark">{{ $booking->notes }}</div>
                                        </div>
                                    @else
                                        <div class="text-muted small fst-italic">{{ __('No resident notes provided') }}</div>
                                    @endif
                                </div>
                            </div>

                            {{-- Resident & Location Side --}}
                            <div class="col-md-6">
                                <div class="small fw-bold text-secondary mb-2 text-uppercase" style="letter-spacing: 0.04em;">
                                    {{ __('Resident and home') }}
                                </div>
                                <div class="d-grid gap-2 small">
                                    <div class="row g-2 align-items-center py-1">
                                        <div class="col-4 text-secondary">{{ __('Resident') }}:</div>
                                        <div class="col-8"><strong class="text-dark">{{ $booking->resident?->name ?? '—' }}</strong></div>
                                    </div>

                                    @if($booking->resident?->phone)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-4 text-secondary">{{ __('Mobile') }}:</div>
                                            <div class="col-8 d-flex align-items-center gap-1">
                                                <a href="tel:{{ $booking->resident->phone }}" class="text-decoration-none text-dark fw-semibold" dir="ltr">{{ $booking->resident->phone }}</a>
                                                <a href="tel:{{ $booking->resident->phone }}" class="btn btn-sm btn-outline-secondary py-0 px-1" title="{{ __('Call resident') }}">
                                                    <i class="fa-solid fa-phone-flip text-primary" style="font-size:0.7rem;"></i>
                                                </a>
                                                <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success py-0 px-1" title="{{ __('Contact via WhatsApp') }}">
                                                    <i class="fa-brands fa-whatsapp text-success" style="font-size:0.75rem;"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endif

                                    @if($booking->resident?->email)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-4 text-secondary">{{ __('Email') }}:</div>
                                            <div class="col-8">
                                                <a href="mailto:{{ $booking->resident->email }}" class="text-decoration-none text-dark text-truncate d-block" title="{{ $booking->resident->email }}">{{ $booking->resident->email }}</a>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="row g-2 align-items-center py-1">
                                        <div class="col-4 text-secondary">{{ __('Property') }}:</div>
                                        <div class="col-8 fw-semibold text-dark">{{ $booking->home?->name ?? '—' }} ({{ $cityName }})</div>
                                    </div>

                                    @if($booking->residentHome)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-4 text-secondary">{{ __('Unit label') }}:</div>
                                            <div class="col-8"><strong class="text-dark">{{ $booking->residentHome->unit_label }}</strong></div>
                                        </div>
                                        @if($booking->residentHome->floor_number)
                                            <div class="row g-2 align-items-center py-1">
                                                <div class="col-4 text-secondary">{{ __('Floor') }}:</div>
                                                <div class="col-8 text-dark">{{ $booking->residentHome->floor_number }}</div>
                                            </div>
                                        @endif
                                    @endif

                                    @if($booking->home?->address)
                                        <div class="row g-2 align-items-center py-1">
                                            <div class="col-4 text-secondary">{{ __('Address') }}:</div>
                                            <div class="col-8 text-dark text-truncate" title="{{ $booking->home->address }}">{{ $booking->home->address }}</div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2.2 Attached Photos & Customer Review Card --}}
                    <div class="panel p-3 p-lg-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-images text-info"></i>
                                <span>{{ __('Attached photos and media') }}</span>
                                <span class="badge rounded-pill bg-light text-secondary border ms-1">
                                    {{ $booking->bookingMedia->count() }}
                                </span>
                            </h2>
                            @if($booking->bookingRatings->isNotEmpty())
                                <span class="badge bg-warning-subtle text-dark border border-warning">
                                    <i class="fa-solid fa-star text-warning me-1"></i>{{ __('Customer rating') }}
                                </span>
                            @endif
                        </div>

                        {{-- Photos Grid --}}
                        @if($booking->bookingMedia->isNotEmpty())
                            <div class="row g-2 mb-3">
                                @foreach($booking->bookingMedia as $media)
                                    <div class="col-6 col-sm-4 col-md-3">
                                        <div class="card h-100 border shadow-sm overflow-hidden" style="border-radius:10px;">
                                            <a href="{{ $media->url }}" target="_blank" rel="noopener noreferrer"
                                               class="d-block position-relative ratio ratio-1x1 bg-light photo-preview-link"
                                               title="{{ __('View full photo') }}"
                                               data-img-url="{{ $media->url }}"
                                               data-img-kind="{{ $media->kind ?? 'photo' }}">
                                                <img src="{{ $media->url }}" alt="Booking photo" class="w-100 h-100" style="object-fit:cover;">
                                                <span class="position-absolute top-0 end-0 m-1 badge bg-dark bg-opacity-75" style="font-size:0.65rem;">
                                                    <i class="fa-solid fa-magnifying-glass-plus"></i>
                                                </span>
                                            </a>
                                            <div class="p-1 px-2 small text-secondary bg-white d-flex justify-content-between align-items-center" style="font-size:0.7rem;">
                                                <span class="text-truncate fw-semibold text-dark">{{ $media->kind ?? 'photo' }}</span>
                                                <span class="text-muted">{{ $media->created_at ? $media->created_at->format('H:i') : '' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 text-center text-muted bg-light rounded-3 small mb-3">
                                <i class="fa-regular fa-image me-1"></i> {{ __('No photos attached yet') }}
                            </div>
                        @endif

                        {{-- Customer Review & Rating (if exists) --}}
                        @if($booking->bookingRatings->isNotEmpty())
                            <div class="pt-2 border-top">
                                <div class="small fw-bold text-dark mb-2 d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-star text-warning"></i>
                                    <span>{{ __('Customer review and rating') }}</span>
                                </div>
                                @foreach($booking->bookingRatings as $rating)
                                    <div class="p-3 rounded-3" style="background:var(--sand); border: 1px solid #ffe8cc;">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <div class="d-flex align-items-center gap-1 text-warning fs-6">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa-{{ $i <= $rating->score ? 'solid' : 'regular' }} fa-star"></i>
                                                @endfor
                                                <span class="ms-1 fw-bold text-dark" style="font-size:0.85rem;">{{ $rating->score }} / 5</span>
                                            </div>
                                            <div class="small text-muted" style="font-size:0.75rem;">
                                                {{ $rating->created_at ? $rating->created_at->format('Y-m-d') : '' }}
                                            </div>
                                        </div>
                                        @if($rating->comment)
                                            <div class="text-dark fst-italic small mt-1">"{{ $rating->comment }}"</div>
                                        @endif
                                        @if(!empty($rating->tags))
                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                @foreach($rating->tags as $tag)
                                                    <span class="badge bg-white text-dark border px-2 py-1 rounded-pill" style="font-size: 0.75rem;">
                                                        <i class="fa-solid fa-check text-success me-1"></i>{{ $tag }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- 2.3 Additional Works & Warranties (if any) --}}
                    @if($booking->bookingAdditionalWork->isNotEmpty() || $booking->bookingWarranties->isNotEmpty())
                        <div class="panel p-3 p-lg-4 mb-3">
                            <h2 class="h6 fw-bold mb-3 border-bottom pb-2 text-dark">
                                <i class="fa-solid fa-shield-halved me-1 text-success"></i>{{ __('Additional work') }} & {{ __('Warranties') }}
                            </h2>
                            @if($booking->bookingAdditionalWork->isNotEmpty())
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle mb-0" style="font-size: 0.8rem;">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Record number') }}</th>
                                                <th>{{ __('Description') }}</th>
                                                <th>{{ __('Amount') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($booking->bookingAdditionalWork as $work)
                                                <tr>
                                                    <td><strong>{{ $work->reference }}</strong></td>
                                                    <td>{{ $work->description }}</td>
                                                    <td><strong class="text-primary">{{ number_format($work->amount, 2) }} SAR</strong></td>
                                                    <td><span class="pill py-0 px-2">{{ $work->status?->translations->firstWhere('locale', $locale)?->name ?? $work->status?->code }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Sidebar Column (4 cols): Quick Update Form, Financials & Timeline --}}
                <div class="col-lg-4">
                    {{-- 3.1 Quick Order Update Form --}}
                    @adminCan('admin.service-orders.update', 'PUT')
                        <div class="panel p-3 p-lg-4 mb-3 shadow-sm" style="border: 1.5px solid var(--petrol); border-radius: 14px;">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:30px; height:30px; background:var(--mint); color:var(--petrol);">
                                        <i class="fa-solid fa-sliders" style="font-size:0.8rem;"></i>
                                    </div>
                                    <h2 class="h6 fw-bold mb-0 text-dark">
                                        {{ __('Quick order update') }}
                                    </h2>
                                </div>
                                <span class="badge" style="background:var(--petrol); color:#fff; font-size:0.7rem;">{{ __('Update') }}</span>
                            </div>

                            <form method="post" action="{{ route('admin.service-orders.update', $booking->id) }}" enctype="multipart/form-data" class="d-grid gap-2">
                                @csrf
                                @method('put')

                                <div>
                                    <label class="form-label fw-semibold text-dark small mb-1">
                                        <i class="fa-solid fa-list-check text-primary me-1"></i>{{ __('Booking status') }} <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select form-select-sm order-status" name="booking_status_id" required>
                                        @foreach($statuses as $status)
                                            <option value="{{ $status->id }}" data-code="{{ $status->code }}" @selected($booking->booking_status_id === $status->id)>
                                                {{ $status->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold text-dark small mb-1">
                                        <i class="fa-solid fa-handshake text-primary me-1"></i>{{ __('Assigned provider') }}
                                    </label>
                                    <select class="form-select form-select-sm provider-select" name="provider_profile_id">
                                        <option value="">{{ __('Choose provider') }}</option>
                                        @foreach($providers as $provider)
                                            <option value="{{ $provider->id }}" @selected(($assignment?->provider_profile_id ?? $booking->service_provider_id) === $provider->id)>
                                                {{ $provider->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold text-dark small mb-1">
                                        <i class="fa-solid fa-coins text-primary me-1"></i>{{ __('Final amount for commission') }}
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" step=".01" min="0" max="9999999999.99" name="gross_amount" class="form-control gross-amount"
                                               value="{{ old('gross_amount', $commission?->gross_amount ?? $booking->total_amount) }}"
                                               placeholder="{{ __('Final amount for commission') }}">
                                        <span class="input-group-text bg-light text-secondary">SAR</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold text-dark small mb-1">
                                        <i class="fa-solid fa-note-sticky text-primary me-1"></i>{{ __('Internal team note') }}
                                    </label>
                                    <textarea name="note" rows="2" maxlength="2000" class="form-control form-control-sm" placeholder="{{ __('Internal note') }}"></textarea>
                                </div>

                                <div>
                                    <label class="form-label fw-semibold text-dark small mb-1">
                                        <i class="fa-solid fa-camera text-primary me-1"></i>{{ __('Add photos') }}
                                    </label>
                                    <input type="file" name="images[]" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp,image/jpg" multiple>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm w-100 py-2 fw-semibold shadow-sm mt-1">
                                    <i class="fa-solid fa-floppy-disk me-1"></i> {{ __('Save order update') }}
                                </button>
                            </form>
                        </div>
                    @endadminCan

                    {{-- 3.2 Financial & Payments Card (Compact) --}}
                    <div class="panel p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-wallet text-success"></i>
                                <span>{{ __('Financial and commission') }}</span>
                            </h2>
                            <span class="badge bg-light text-secondary border font-monospace">
                                {{ number_format($booking->total_amount, 2) }} SAR
                            </span>
                        </div>

                        <div class="d-grid gap-1 small">
                            <div class="d-flex justify-content-between">
                                <span class="text-secondary">{{ __('Provider') }}:</span>
                                <span class="fw-semibold text-dark text-truncate" style="max-width: 170px;" title="{{ $providerProfile?->company_name ?? __('Not assigned yet') }}">
                                    {{ $providerProfile?->company_name ?? __('Not assigned yet') }}
                                </span>
                            </div>

                            @if($commission)
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">{{ __('Commission rate') }}:</span>
                                    <span>{{ $commission->commission_percent_snapshot }}%</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">{{ __('Commission amount') }}:</span>
                                    <strong class="text-success">{{ number_format($commission->commission_amount, 2) }} SAR</strong>
                                </div>
                            @elseif($assignment?->providerContract)
                                <div class="d-flex justify-content-between">
                                    <span class="text-secondary">{{ __('Commission rate') }}:</span>
                                    <span>{{ $assignment->providerContract->service_commission_percent }}%</span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between border-top pt-1 mt-1">
                                <span class="text-secondary">{{ __('Payments') }}:</span>
                                <span>{{ $booking->payments->isNotEmpty() ? $booking->payments->count() : __('No payments recorded') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3.3 Timeline & Status History Card (Compact) --}}
                    <div class="panel p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1">
                                <i class="fa-solid fa-timeline text-secondary"></i>
                                <span>{{ __('Timeline and status history') }}</span>
                            </h2>
                            <span class="badge bg-light text-secondary border">{{ $booking->bookingStatusHistory->count() }}</span>
                        </div>

                        @if($booking->bookingStatusHistory->isNotEmpty())
                            <div class="position-relative ps-3" style="border-inline-start: 2px dashed #b4dbd2; margin-inline-start: 8px; max-height: 240px; overflow-y: auto;">
                                @foreach($booking->bookingStatusHistory as $history)
                                    @php
                                        $hStatusTranslation = $history->status?->translations->firstWhere('locale', $locale);
                                        $hStatusName = $hStatusTranslation?->name ?? $history->status?->code ?? '—';
                                        $hCode = $history->status?->code ?? 'pending';
                                        $actorName = $history->adminActor?->name ?? $history->changedBy?->name ?? __('System / User');

                                        $dotColor = match($hCode) {
                                            'completed' => '#10b981',
                                            'in_progress' => 'var(--orange)',
                                            'confirmed' => '#025968',
                                            'cancelled' => '#ef4444',
                                            default => '#f59e0b',
                                        };
                                    @endphp
                                    <div class="position-relative mb-2 pb-1" style="font-size:0.75rem;">
                                        <span class="position-absolute rounded-circle"
                                              style="width: 10px; height: 10px; inset-inline-start: -21px; top: 3px; border: 2px solid #fff; background-color: {{ $dotColor }};"></span>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="fw-semibold text-dark">{{ $hStatusName }}</span>
                                            <span class="text-muted" style="font-size: 0.68rem;">{{ $history->created_at ? $history->created_at->format('m-d H:i') : '' }}</span>
                                        </div>
                                        <div class="text-secondary" style="font-size: 0.7rem;">
                                            {{ $actorName }}
                                        </div>
                                        @if($history->note)
                                            <div class="bg-light p-1 rounded mt-1 text-dark fst-italic" style="font-size: 0.7rem;">
                                                "{{ $history->note }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-2 text-center text-muted bg-light rounded-3 small">
                                {{ __('No status history recorded') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- Lightbox Image Modal for Fast Photo Preview --}}
<div class="modal fade d-print-none" id="bookingPhotoModal" tabindex="-1" aria-labelledby="bookingPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header border-bottom py-2 px-3" style="background:#f8faf9;">
                <h5 class="modal-title fs-6 fw-bold text-dark" id="bookingPhotoModalLabel">
                    <i class="fa-solid fa-image me-1 text-primary"></i> <span id="photoModalTitle">{{ __('Attached photos and media') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-dark">
                <img id="photoModalImage" src="" alt="Booking Photo" class="img-fluid" style="max-height: 80vh; width: auto; object-fit: contain;">
            </div>
            <div class="modal-footer border-top py-2 px-3 justify-content-between" style="background:#f8faf9;">
                <a id="photoModalRawLink" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('Open original image') }}
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                    {{ __('Close preview') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.order-status').forEach((select) => {
    const form = select.closest('form');
    if (!form) return;
    const amount = form.querySelector('.gross-amount');
    if (!amount) return;
    const sync = () => {
        const complete = select.options[select.selectedIndex]?.dataset.code === 'completed';
        amount.required = complete;
        amount.placeholder = complete ? @json(__('Final amount for commission')) : @json(__('Optional final amount'));
    };
    select.addEventListener('change', sync);
    sync();
});

// Photo Lightbox Modal handler
document.querySelectorAll('.photo-preview-link').forEach((link) => {
    link.addEventListener('click', function(e) {
        if (window.bootstrap && window.bootstrap.Modal) {
            e.preventDefault();
            const modalEl = document.getElementById('bookingPhotoModal');
            const modalImg = document.getElementById('photoModalImage');
            const modalTitle = document.getElementById('photoModalTitle');
            const rawLink = document.getElementById('photoModalRawLink');
            
            const url = this.getAttribute('data-img-url') || this.href;
            const kind = this.getAttribute('data-img-kind') || '';
            
            modalImg.src = url;
            rawLink.href = url;
            if (kind) {
                modalTitle.textContent = kind;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    });
});
</script>
@endpush
