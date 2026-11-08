@extends('layouts.admin')
@section('title', __('Edit company').' · '.$provider->company_name.' · Rounder')
@section('body')
@php
    $tab1Errors = $errors->hasAny(['company_name','trade_name','provider_type','years_of_experience','name','phone','email','locale','is_approved','commercial_registration','legal_representative','legal_representative_id']);
    $tab2Errors = $errors->hasAny(['service_ids','service_ids.*']);
    $tab3Errors = $errors->hasAny(array_keys(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS));
    $tab4Errors = $errors->hasAny(['service_commission_percent','emergency_commission_percent','starts_on','ends_on','terms']);
    $activeTab = 'company';
    if (!$tab1Errors) {
        if ($tab2Errors) $activeTab = 'services';
        elseif ($tab3Errors) $activeTab = 'documents';
        elseif ($tab4Errors) $activeTab = 'contract';
    }
@endphp
<style>
.provider-tabs-nav {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 10px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
    box-shadow: 0 4px 20px rgba(2, 51, 62, 0.05);
}
@media (max-width: 991.98px) {
    .provider-tabs-nav {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media (max-width: 575.98px) {
    .provider-tabs-nav {
        grid-template-columns: 1fr;
    }
}
.provider-tabs-nav .nav-item {
    margin: 0;
    min-width: 0;
}
.provider-tabs-nav .nav-link {
    width: 100%;
    height: 100%;
    border-radius: 14px;
    padding: 14px 16px;
    color: #334155;
    border: 1.5px solid #eef2f6;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.22s ease;
    text-align: start;
    position: relative;
    overflow: hidden;
}
.provider-tabs-nav .nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    left: 0;
    height: 3px;
    background: transparent;
    transition: background 0.22s ease;
}
.provider-tabs-nav .nav-link:hover {
    background: #fffaf5;
    color: #02333E;
    border-color: #fbd3b6;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(251, 130, 47, 0.08);
}
.provider-tabs-nav .nav-link.has-tab-error {
    border-color: #f87171 !important;
    background: #fef2f2 !important;
    color: #991b1b !important;
}
.provider-tabs-nav .nav-link.has-tab-error::after {
    background: #dc2626;
}
.provider-tabs-nav .nav-link.active {
    background: linear-gradient(135deg, #02333E 0%, #054a59 100%) !important;
    color: #ffffff !important;
    border-color: #02333E !important;
    box-shadow: 0 8px 20px rgba(2, 51, 62, 0.2);
    transform: translateY(-1px);
}
.provider-tabs-nav .nav-link.active::after {
    background: #FB822F;
}
.provider-tabs-nav .step-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: #f1f5f9;
    color: #02333E;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex-shrink: 0;
    position: relative;
    transition: all 0.22s ease;
    border: 1px solid #e2e8f0;
}
.provider-tabs-nav .nav-link:hover .step-icon-box {
    background: #fff0e5;
    color: #FB822F;
    border-color: #fbd3b6;
}
.provider-tabs-nav .nav-link.active .step-icon-box {
    background: #FB822F;
    color: #ffffff;
    border-color: #FB822F;
    box-shadow: 0 4px 10px rgba(251, 130, 47, 0.35);
}
.provider-tabs-nav .step-num-pill {
    font-size: 0.68rem;
    font-weight: 800;
    letter-spacing: 0.02em;
    color: #FB822F;
    background: rgba(251, 130, 47, 0.1);
    padding: 2px 8px;
    border-radius: 999px;
    display: inline-block;
    margin-bottom: 3px;
}
.provider-tabs-nav .nav-link.active .step-num-pill {
    color: #ffd8bf;
    background: rgba(255, 255, 255, 0.14);
}
.provider-tabs-nav .step-title {
    font-weight: 800;
    font-size: 0.94rem;
    line-height: 1.25;
    margin-bottom: 2px;
}
.provider-tabs-nav .step-sub {
    font-size: 0.76rem;
    opacity: 0.75;
    font-weight: 500;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.doc-upload-card {
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 14px 16px;
    background: #f8fafc;
    transition: border-color 0.2s ease, background 0.2s ease;
}
.doc-upload-card:hover {
    border-color: #FB822F;
    background: #fffaf5;
}
.doc-upload-card.has-error {
    border-color: #dc3545;
    background: #fff5f5;
}
.doc-upload-card.has-file {
    border-color: #16a34a;
    border-style: solid;
    background: #f0fdf4;
}
</style>

<main class="container py-4">
    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                @adminCan('admin.providers.index')
                <a class="btn btn-sm btn-outline-secondary py-1 px-3 rounded-pill" href="{{ route('admin.providers.index') }}">
                    <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                    {{ __('Providers and contracts') }}
                </a>
                @endadminCan
                <span class="badge" style="background: rgba(251, 130, 47, 0.12); color: var(--orange); font-weight: 700; border: 1px solid rgba(251, 130, 47, 0.25);">
                    #{{ $provider->id }}
                </span>
                @if($provider->is_approved)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ __('Approved') }}
                    </span>
                @else
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                        <i class="fa-solid fa-hourglass-half me-1"></i>{{ __('Pending approval') }}
                    </span>
                @endif
            </div>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2 flex-wrap">
                <i class="fa-solid fa-handshake text-rounder-orange" style="font-size: 1.4rem;"></i>
                <span>{{ __('Edit company') }}:</span>
                <span class="text-rounder-orange">{{ $provider->company_name }}</span>
            </h1>
            <p class="text-secondary mb-0 small">{{ __('Company edits do not change contract terms or accrued commissions.') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.providers.contract.pdf', $provider->id) }}" class="btn btn-danger shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="fa-solid fa-file-pdf"></i>
                <span>{{ __('Download contract PDF') }}</span>
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')
    @if($errors->any())
        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 14px;">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-xmark me-1"></i>{{ __('Please correct the errors below:') }}</div>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @adminCan('admin.providers.update', 'PUT')
    <form method="post" action="{{ route('admin.providers.update', $provider->id) }}" id="edit-provider-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- 4-Step Tabs Navigation --}}
        <ul class="nav nav-pills provider-tabs-nav mb-4" id="providerFormTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'company' ? 'active' : '' }} {{ $tab1Errors ? 'has-tab-error' : '' }}" id="tab-company-btn" data-bs-toggle="pill" data-bs-target="#tab-company" type="button" role="tab">
                    <span class="step-icon-box"><i class="fa-solid fa-building-user"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <span class="step-num-pill">الخطوة ١</span>
                        <div class="step-title">بيانات الشركة والممثل</div>
                        <small class="step-sub">الحساب والسجل التجاري</small>
                    </div>
                    @if($tab1Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'services' ? 'active' : '' }} {{ $tab2Errors ? 'has-tab-error' : '' }}" id="tab-services-btn" data-bs-toggle="pill" data-bs-target="#tab-services" type="button" role="tab">
                    <span class="step-icon-box"><i class="fa-solid fa-screwdriver-wrench"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <span class="step-num-pill">الخطوة ٢</span>
                        <div class="step-title">الخدمات المقدمة</div>
                        <small class="step-sub">تخصصات الصيانة المعتمدة</small>
                    </div>
                    @if($tab2Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }} {{ $tab3Errors ? 'has-tab-error' : '' }}" id="tab-documents-btn" data-bs-toggle="pill" data-bs-target="#tab-documents" type="button" role="tab">
                    <span class="step-icon-box"><i class="fa-solid fa-folder-open"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between gap-1">
                            <span class="step-num-pill">الخطوة ٣</span>
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary border" id="docs-counter-badge" style="font-size: 0.7rem;">0 / {{ count(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS) }}</span>
                        </div>
                        <div class="step-title">المرفقات والمستندات</div>
                        <small class="step-sub">السجل والختم والهوية</small>
                    </div>
                    @if($tab3Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $activeTab === 'contract' ? 'active' : '' }} {{ $tab4Errors ? 'has-tab-error' : '' }}" id="tab-contract-btn" data-bs-toggle="pill" data-bs-target="#tab-contract" type="button" role="tab">
                    <span class="step-icon-box"><i class="fa-solid fa-file-signature"></i></span>
                    <div class="flex-grow-1 min-w-0">
                        <span class="step-num-pill">الخطوة ٤</span>
                        <div class="step-title">العقد والعمولات</div>
                        <small class="step-sub">بنود عقد راوندر والعمولة</small>
                    </div>
                    @if($tab4Errors)<span class="badge bg-danger rounded-pill">!</span>@endif
                </button>
            </li>
        </ul>

        <div class="tab-content" id="providerFormTabContent">
            {{-- TAB 1: Company & Contact Details + Legal Rep --}}
            <div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="tab-company" role="tabpanel">
                <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Company and contact details') }}</h5>
                            <div class="text-muted small">{{ __('Basic registered information for the partner company.') }}</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Company name') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-city"></i></span>
                                <input class="form-control @error('company_name') is-invalid @enderror" name="company_name" required minlength="2" maxlength="160" value="{{ old('company_name', $provider->company_name) }}" placeholder="{{ __('Company name') }}">
                            </div>
                            @error('company_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Commercial name') }} <span class="text-danger approved-req-star d-none">*</span></label>
                            <input class="form-control @error('trade_name') is-invalid @enderror" name="trade_name" maxlength="160" value="{{ old('trade_name', $provider->trade_name) }}">
                            @error('trade_name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Provider type') }}</label>
                            <select class="form-select @error('provider_type') is-invalid @enderror" name="provider_type" id="provider_type_select">
                                <option value="">{{ __('Choose provider type') }}</option>
                                @foreach(['فني مستقل' => __('Independent technician'), 'مؤسسة خدمات' => __('Service establishment'), 'شركة صيانة' => __('Maintenance company')] as $value => $type)
                                    <option value="{{ $value }}" @selected(old('provider_type', $provider->provider_type) === $value)>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('provider_type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Years of experience') }}</label>
                            <input class="form-control @error('years_of_experience') is-invalid @enderror" type="number" name="years_of_experience" min="0" max="100" value="{{ old('years_of_experience', $provider->years_of_experience) }}">
                            @error('years_of_experience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Contact name') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-user-tie"></i></span>
                                <input class="form-control @error('name') is-invalid @enderror" name="name" required minlength="2" maxlength="120" value="{{ old('name', $provider->user->name) }}" placeholder="{{ __('Contact name') }}">
                            </div>
                            @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Phone') }} (05…) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-phone"></i></span>
                                <input type="tel" dir="ltr" class="form-control font-monospace @error('phone') is-invalid @enderror" name="phone" required pattern="05[0-9]{8}" maxlength="10" placeholder="05XXXXXXXX" value="{{ old('phone', $provider->user->phone) }}">
                            </div>
                            @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Email address') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" class="form-control font-monospace @error('email') is-invalid @enderror" name="email" maxlength="254" value="{{ old('email', $provider->user->email) }}" placeholder="name@company.com">
                            </div>
                            @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">{{ __('Account language') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('locale') is-invalid @enderror" name="locale" required>
                                <option value="ar" @selected(old('locale', $provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='ar')>العربية</option>
                                <option value="en" @selected(old('locale', $provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='en')>English</option>
                                <option value="ur" @selected(old('locale', $provider->user->locale instanceof \BackedEnum ? $provider->user->locale->value : $provider->user->locale)==='ur')>اردو</option>
                            </select>
                            @error('locale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label fw-semibold">{{ __('Approval') }} <span class="text-danger">*</span></label>
                            <select class="form-select @error('is_approved') is-invalid @enderror" name="is_approved" id="is_approved_select" required>
                                <option value="0" @selected(old('is_approved', $provider->is_approved)==0)>{{ __('Pending approval') }}</option>
                                <option value="1" @selected(old('is_approved', $provider->is_approved)==1)>{{ __('Approved') }}</option>
                            </select>
                            @error('is_approved')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px;">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#eaf4f6;color:#02333E;font-size:1.05rem;">
                            <i class="fa-solid fa-id-card-clip"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Commercial and legal representative details') }}</h5>
                            <div class="text-muted small">تُدرج هذه البيانات تلقائياً في بنود عقد مزود الخدمة (الطرف الثاني).</div>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('Commercial register') }} <span class="text-danger approved-req-star d-none">*</span></label>
                            <input class="form-control @error('commercial_registration') is-invalid @enderror" name="commercial_registration" maxlength="80" value="{{ old('commercial_registration', $provider->commercial_registration) }}">
                            @error('commercial_registration')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('Legal representative') }} <span class="text-danger approved-req-star d-none">*</span></label>
                            <input class="form-control @error('legal_representative') is-invalid @enderror" name="legal_representative" maxlength="160" value="{{ old('legal_representative', $provider->legal_representative) }}">
                            @error('legal_representative')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">{{ __('Representative national ID') }} <span class="text-danger approved-req-star d-none">*</span></label>
                            <input class="form-control @error('legal_representative_id') is-invalid @enderror" name="legal_representative_id" maxlength="30" value="{{ old('legal_representative_id', $provider->legal_representative_id) }}">
                            @error('legal_representative_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-services-btn" data-validate-current="true">
                            <span>التالي: الخدمات المقدمة</span>
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB 2: Services --}}
            <div class="tab-pane fade {{ $activeTab === 'services' ? 'show active' : '' }}" id="tab-services" role="tabpanel">
                <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                    @include('admin.providers.partials.services-field')

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-company-btn">
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                            <span>السابق: بيانات الشركة</span>
                        </button>
                        <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-documents-btn" data-validate-current="true">
                            <span>التالي: المرفقات والمستندات</span>
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB 3: Documents --}}
            <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="tab-documents" role="tabpanel">
                <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                            <i class="fa-solid fa-folder-open"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ __('Provider documents (PDF or image, maximum 10 MB)') }}</h5>
                            <div class="text-muted small">إرفاق أو تحديث المستندات الرسمية المطلوبة لاعتماد مزود الخدمة.</div>
                        </div>
                    </div>

                    <div class="row g-3">
                        @foreach(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS as $field => $label)
                            <div class="col-md-6">
                                <div class="doc-upload-card h-100 {{ $provider->$field ? 'has-file' : '' }} @error($field) has-error @enderror" data-doc-card="{{ $field }}" data-has-saved="{{ $provider->$field ? '1' : '0' }}">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label fw-semibold text-dark d-flex align-items-center gap-2 mb-0" for="{{ $field }}">
                                            <i class="fa-solid {{ $provider->$field ? 'fa-file-circle-check text-success' : 'fa-file-arrow-up text-rounder-orange' }} doc-status-icon"></i>
                                            <span>{{ __($label) }}</span>
                                            @if(!$provider->$field)
                                                <span class="text-danger approved-req-star d-none">*</span>
                                            @endif
                                        </label>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle d-none doc-ready-badge">
                                                <i class="fa-solid fa-check me-1"></i>ملف جديد
                                            </span>
                                            @if($provider->$field)
                                                <a class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none px-2 py-1" href="{{ route('admin.providers.documents.show', [$provider->id, $field]) }}">
                                                    <i class="fa-solid fa-download me-1"></i>{{ __($label) }} ({{ __('View attachment') }})
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <input class="form-control doc-file-input @error($field) is-invalid @enderror" type="file" id="{{ $field }}" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png">
                                    <div class="doc-file-selected-info d-none mt-2 p-2 rounded-2 bg-white border border-success-subtle d-flex align-items-center justify-content-between gap-2 small">
                                        <span class="text-success fw-semibold text-truncate d-flex align-items-center gap-1">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span class="doc-file-name text-truncate"></span>
                                        </span>
                                        <span class="badge bg-light text-dark border font-monospace doc-file-size"></span>
                                    </div>
                                    <div class="small text-muted mt-1">PDF, JPG, PNG (Max: 10MB)</div>
                                    @error($field)<div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-services-btn">
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                            <span>السابق: الخدمات المقدمة</span>
                        </button>
                        <button type="button" class="btn btn-primary px-4" data-switch-tab="tab-contract-btn" data-validate-current="true">
                            <span>التالي: العقد والعمولات</span>
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-left' : 'fa-arrow-right' }} ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- TAB 4: Contract & Commissions --}}
            <div class="tab-pane fade {{ $activeTab === 'contract' ? 'show active' : '' }}" id="tab-contract" role="tabpanel">
                @if($contract)
                <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                <div class="panel p-3 p-lg-4 mb-4 shadow-sm" style="border-radius: 18px; border-top: 4px solid var(--orange);">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center" style="width:36px;height:36px;border-radius:10px;background:#fff3eb;color:var(--orange);font-size:1.05rem;">
                                <i class="fa-solid fa-file-contract"></i>
                            </span>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">{{ __('Contract terms and commissions') }}</h5>
                                <div class="text-muted small">
                                    {{ __('Contract') }}: <strong class="text-dark font-monospace">{{ $contract->reference }}</strong>
                                    · <span class="badge {{ $contract->status?->code === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-secondary border' }}">{{ __($contract->status?->code ?? 'active') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pill px-3 py-1.5" style="background:#fff8f3; border-color:#ffd4b8; color:var(--orange);">
                                <i class="fa-solid fa-coins me-1"></i>
                                {{ __('Commission accrued') }}: <strong class="text-dark">{{ number_format($provider->commission_total ?? 0, 2) }} SAR</strong>
                            </div>
                            <a href="{{ route('admin.providers.contract.pdf', $provider->id) }}" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-file-pdf"></i>
                                <span>{{ __('Download contract PDF') }}</span>
                            </a>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Service commission (%)') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-percent"></i></span>
                                <input type="number" name="service_commission_percent" class="form-control font-monospace @error('service_commission_percent') is-invalid @enderror" step=".01" min="0" max="100" value="{{ old('service_commission_percent', $contract->service_commission_percent) }}" required>
                                <span class="input-group-text bg-white text-muted">%</span>
                            </div>
                            @error('service_commission_percent')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Emergency commission (%)') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-solid fa-bolt text-rounder-orange"></i></span>
                                <input type="number" name="emergency_commission_percent" class="form-control font-monospace @error('emergency_commission_percent') is-invalid @enderror" step=".01" min="0" max="100" value="{{ old('emergency_commission_percent', $contract->emergency_commission_percent) }}" required>
                                <span class="input-group-text bg-white text-muted">%</span>
                            </div>
                            @error('emergency_commission_percent')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Contract starts on') }} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-regular fa-calendar-check text-success"></i></span>
                                <input type="date" name="starts_on" class="form-control @error('starts_on') is-invalid @enderror" value="{{ old('starts_on', $contract->starts_on?->format('Y-m-d')) }}" required>
                            </div>
                            @error('starts_on')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">{{ __('Contract ends on') }}</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary"><i class="fa-regular fa-calendar-xmark text-danger"></i></span>
                                <input type="date" name="ends_on" class="form-control @error('ends_on') is-invalid @enderror" value="{{ old('ends_on', $contract->ends_on?->format('Y-m-d')) }}">
                            </div>
                            @error('ends_on')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 mt-3">
                            @include('admin.providers.partials.terms-editor', ['terms' => $contract->terms])
                            @error('terms')<div class="text-danger small mt-1 fw-semibold">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="alert mb-3 d-flex align-items-center gap-2" style="background: #fff8f0; color: #a35610; border: 1px solid #ffd8bf; border-inline-start: 4px solid var(--orange);">
                        <i class="fa-solid fa-circle-info text-rounder-orange fs-5"></i>
                        <span class="small">{{ __('Contract edits apply to future calculations. Accrued commissions stay unchanged.') }}</span>
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <button type="button" class="btn btn-outline-secondary px-4" data-switch-tab="tab-documents-btn">
                            <i class="fa-solid {{ app()->getLocale() === 'ar' ? 'fa-arrow-right' : 'fa-arrow-left' }} me-1"></i>
                            <span>السابق: المرفقات والمستندات</span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Persistent Bottom Action Bar --}}
        <div class="d-flex align-items-center justify-content-between p-3 panel shadow-sm flex-wrap gap-2 mb-4" style="border-radius: 16px;">
            <a href="{{ route('admin.providers.index') }}" class="btn btn-outline-secondary px-4">
                <i class="fa-solid fa-xmark me-1"></i> {{ __('Cancel') }}
            </a>
            <button type="submit" class="btn btn-brand px-5 fw-bold shadow-sm" style="font-size: 0.95rem;">
                <i class="fa-solid fa-floppy-disk me-1.5"></i> {{ __('Save changes') }}
            </button>
        </div>
    </form>
    @endadminCan
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('edit-provider-form');
    if (!form) return;

    const approvalSelect = document.getElementById('is_approved_select');
    const typeSelect = document.getElementById('provider_type_select');
    function updateRequiredStars() {
        const isStrict = approvalSelect && approvalSelect.value === '1' && typeSelect && typeSelect.value !== '';
        document.querySelectorAll('.approved-req-star').forEach(el => {
            el.classList.toggle('d-none', !isStrict);
        });
    }
    if (approvalSelect) approvalSelect.addEventListener('change', updateRequiredStars);
    if (typeSelect) typeSelect.addEventListener('change', updateRequiredStars);
    updateRequiredStars();

    // تحديث حالة كروت المرفقات وعداد التاب عند اختيار ملف
    const docInputs = form.querySelectorAll('.doc-file-input');
    const docsCounterBadge = document.getElementById('docs-counter-badge');
    const totalDocsCount = docInputs.length;

    function formatBytes(bytes) {
        if (!bytes) return '0 KB';
        const kb = bytes / 1024;
        return kb >= 1024 ? (kb / 1024).toFixed(2) + ' MB' : Math.round(kb) + ' KB';
    }

    function updateDocsStatus() {
        let readyCount = 0;
        docInputs.forEach(input => {
            const card = input.closest('.doc-upload-card');
            if (!card) return;
            const hasSaved = card.dataset.hasSaved === '1';
            const file = input.files && input.files[0];
            const infoBox = card.querySelector('.doc-file-selected-info');
            const nameEl = card.querySelector('.doc-file-name');
            const sizeEl = card.querySelector('.doc-file-size');
            const readyBadge = card.querySelector('.doc-ready-badge');
            const iconEl = card.querySelector('.doc-status-icon');

            if (file) {
                readyCount++;
                card.classList.add('has-file');
                if (infoBox) infoBox.classList.remove('d-none');
                if (nameEl) nameEl.textContent = file.name;
                if (sizeEl) sizeEl.textContent = formatBytes(file.size);
                if (readyBadge) readyBadge.classList.remove('d-none');
                if (iconEl) {
                    iconEl.classList.remove('fa-file-arrow-up', 'text-rounder-orange');
                    iconEl.classList.add('fa-file-circle-check', 'text-success');
                }
            } else {
                if (hasSaved) {
                    readyCount++;
                    card.classList.add('has-file');
                } else {
                    card.classList.remove('has-file');
                }
                if (infoBox) infoBox.classList.add('d-none');
                if (readyBadge) readyBadge.classList.add('d-none');
                if (iconEl && !hasSaved) {
                    iconEl.classList.add('fa-file-arrow-up', 'text-rounder-orange');
                    iconEl.classList.remove('fa-file-circle-check', 'text-success');
                }
            }
        });

        if (docsCounterBadge) {
            docsCounterBadge.textContent = `${readyCount} / ${totalDocsCount}`;
            if (readyCount > 0) {
                docsCounterBadge.className = 'badge rounded-pill bg-success text-white';
            } else {
                docsCounterBadge.className = 'badge rounded-pill bg-secondary-subtle text-secondary border';
            }
        }
    }

    docInputs.forEach(input => input.addEventListener('change', updateDocsStatus));
    updateDocsStatus();

    document.querySelectorAll('[data-switch-tab]').forEach(btn => {
        btn.addEventListener('click', function () {
            if (this.dataset.validateCurrent === 'true') {
                const currentPane = this.closest('.tab-pane');
                if (currentPane) {
                    const inputs = currentPane.querySelectorAll('input, select, textarea');
                    for (const input of inputs) {
                        if (!input.checkValidity()) {
                            input.reportValidity();
                            return;
                        }
                    }
                }
            }
            const targetBtn = document.getElementById(this.dataset.switchTab);
            if (targetBtn) {
                targetBtn.click();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });

    form.addEventListener('invalid', function (e) {
        const invalidEl = e.target;
        const pane = invalidEl.closest('.tab-pane');
        if (pane && !pane.classList.contains('active')) {
            const tabBtn = document.querySelector(`[data-bs-target="#${pane.id}"]`);
            if (tabBtn) {
                tabBtn.click();
                setTimeout(() => {
                    invalidEl.focus();
                    invalidEl.reportValidity();
                }, 150);
            }
        }
    }, true);
});
</script>
@endpush
@endsection
