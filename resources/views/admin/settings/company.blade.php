@extends('layouts.admin')
@section('title', __('Company settings') . ' · Rounder')
@section('body')
<style>
.settings-card {
    background: #ffffff;
    border: 1px solid #e5ebe8;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(2, 51, 62, 0.04);
    transition: box-shadow 0.2s ease;
}
.settings-card:hover {
    box-shadow: 0 4px 16px rgba(2, 51, 62, 0.07);
}
.settings-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #edf2ef;
    background: #fbfdfc;
    border-top-left-radius: 14px;
    border-top-right-radius: 14px;
}
.form-section-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}
.stamp-dropzone {
    border: 2px dashed #cfdbd5;
    border-radius: 12px;
    background: #f8faf9;
    padding: 24px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.stamp-dropzone:hover, .stamp-dropzone.dragover {
    border-color: var(--orange, #fb822f);
    background: #fff8f3;
}
.stamp-preview-box {
    max-width: 240px;
    max-height: 240px;
    background: repeating-conic-gradient(#f0f3f2 0% 25%, #ffffff 0% 50%) 50% / 16px 16px;
    border: 1px solid #dce4e0;
    border-radius: 10px;
    padding: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.stamp-preview-img {
    max-width: 100%;
    max-height: 200px;
    object-fit: contain;
}
</style>

<main class="container-fluid px-3 px-lg-4 py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small text-secondary mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary">{{ __('Dashboard') }}</a></li>
                    <li class="breadcrumb-item">{{ __('System and settings') }}</li>
                    <li class="breadcrumb-item active text-dark fw-semibold" aria-current="page">{{ __('Company settings') }}</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-1 d-flex align-items-center gap-2 text-dark">
                <span class="form-section-icon bg-light-orange text-rounder-orange" style="background:#fff3eb; color:var(--orange, #fb822f);">
                    <i class="fa-solid fa-building-circle-check"></i>
                </span>
                <span>{{ __('Company settings') }}</span>
            </h1>
            <p class="text-secondary small mb-0">{{ __('Company official details and legal representation settings.') }}</p>
        </div>
    </div>

    @include('admin.partials.feedback')

    <form method="POST" action="{{ route('admin.company-settings.update') }}" enctype="multipart/form-data" id="companySettingsForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Column 1: Commercial Data & Legal Representation --}}
            <div class="col-lg-7">
                {{-- Card 1: Commercial & Official Details --}}
                <div class="settings-card mb-4">
                    <div class="settings-card-header d-flex align-items-center gap-3">
                        <span class="form-section-icon bg-white text-primary border" style="color: #02333E !important;">
                            <i class="fa-solid fa-building"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">{{ __('Commercial & Official Info') }}</h2>
                            <small class="text-secondary">{{ __('البيانات الأساسية المسجلة للشركة لدى الجهات الرسمية') }}</small>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <label for="trade_name" class="form-label fw-semibold small text-dark mb-1">
                                {{ __('Commercial name') }} <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-font"></i></span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('trade_name') is-invalid @enderror" 
                                       id="trade_name" 
                                       name="trade_name" 
                                       value="{{ old('trade_name', $settings->trade_name) }}" 
                                       placeholder="{{ __('مثل: شركة راوندر للتشغيل والصيانة') }}" 
                                       required>
                            </div>
                            @error('trade_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cr_number" class="form-label fw-semibold small text-dark mb-1">
                                    {{ __('Commercial register') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-id-card"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('cr_number') is-invalid @enderror" 
                                           id="cr_number" 
                                           name="cr_number" 
                                           value="{{ old('cr_number', $settings->cr_number) }}" 
                                           placeholder="1010XXXXXX" 
                                           dir="ltr"
                                           required>
                                </div>
                                @error('cr_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="tax_number" class="form-label fw-semibold small text-dark mb-1">
                                    {{ __('Tax number') }} <span class="badge bg-light text-secondary border fw-normal">{{ __('Optional') }}</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-receipt"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('tax_number') is-invalid @enderror" 
                                           id="tax_number" 
                                           name="tax_number" 
                                           value="{{ old('tax_number', $settings->tax_number) }}" 
                                           placeholder="300XXXXXXXXXXXX" 
                                           dir="ltr">
                                </div>
                                @error('tax_number')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="trademark_registration" class="form-label fw-semibold small text-dark mb-1">
                                {{ __('Trademark registration') }} <span class="badge bg-light text-secondary border fw-normal">{{ __('Optional') }}</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-certificate"></i></span>
                                <input type="text" 
                                       class="form-control border-start-0 @error('trademark_registration') is-invalid @enderror" 
                                       id="trademark_registration" 
                                       name="trademark_registration" 
                                       value="{{ old('trademark_registration', $settings->trademark_registration) }}" 
                                       placeholder="{{ __('مثل: مسجلة لدى الهيئة السعودية للملكية الفكرية') }}">
                            </div>
                            @error('trademark_registration')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Legal Representation & Title --}}
                <div class="settings-card mb-4">
                    <div class="settings-card-header d-flex align-items-center gap-3">
                        <span class="form-section-icon bg-white text-primary border" style="color: #02333E !important;">
                            <i class="fa-solid fa-user-tie"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">{{ __('Legal & Representation') }}</h2>
                            <small class="text-secondary">{{ __('بيانات الشخص المخول بالتوقيع والتمثيل النظامي للشركة') }}</small>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="legal_representative" class="form-label fw-semibold small text-dark mb-1">
                                    {{ __('Legal representative') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-user-check"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('legal_representative') is-invalid @enderror" 
                                           id="legal_representative" 
                                           name="legal_representative" 
                                           value="{{ old('legal_representative', $settings->legal_representative) }}" 
                                           placeholder="{{ __('الاسم الكامل للممثل القانوني') }}" 
                                           required>
                                </div>
                                @error('legal_representative')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-5">
                                <label for="representative_title" class="form-label fw-semibold small text-dark mb-1">
                                    {{ __('Capacity / Title') }} <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-briefcase"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('representative_title') is-invalid @enderror" 
                                           id="representative_title" 
                                           name="representative_title" 
                                           value="{{ old('representative_title', $settings->representative_title) }}" 
                                           placeholder="{{ __('مثل: المدير العام / المفوض بالتوقيع') }}" 
                                           required>
                                </div>
                                @error('representative_title')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Support & Contact Methods --}}
                <div class="settings-card mb-4" id="contact-settings">
                    <div class="settings-card-header d-flex align-items-center gap-3">
                        <span class="form-section-icon bg-white text-primary border" style="color: #0f4c5c !important;">
                            <i class="fa-solid fa-headset"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">طرق التواصل والدعم الفني (تطبيق الجوال)</h2>
                            <small class="text-secondary">البيانات التي تظهر للسكان في شاشة "تواصل معنا" داخل تطبيق الجوال</small>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="support_phone" class="form-label fw-semibold small text-dark mb-1">
                                    هاتف الدعم (اتصال) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-phone"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('support_phone') is-invalid @enderror" 
                                           id="support_phone" 
                                           name="support_phone" 
                                           value="{{ old('support_phone', $settings->support_phone ?? '920000488') }}" 
                                           placeholder="920000488" 
                                           dir="ltr">
                                </div>
                                @error('support_phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="support_email" class="form-label fw-semibold small text-dark mb-1">
                                    البريد الإلكتروني للدعم <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" 
                                           class="form-control border-start-0 @error('support_email') is-invalid @enderror" 
                                           id="support_email" 
                                           name="support_email" 
                                           value="{{ old('support_email', $settings->support_email ?? 'support@rounder.sa') }}" 
                                           placeholder="support@rounder.sa" 
                                           dir="ltr">
                                </div>
                                @error('support_email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <label for="support_working_hours" class="form-label fw-semibold small text-dark mb-1">
                                    ساعات وأيام عمل الدعم
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-secondary border-end-0"><i class="fa-solid fa-clock"></i></span>
                                    <input type="text" 
                                           class="form-control border-start-0 @error('support_working_hours') is-invalid @enderror" 
                                           id="support_working_hours" 
                                           name="support_working_hours" 
                                           value="{{ old('support_working_hours', $settings->support_working_hours ?? 'الأحد إلى الخميس، 9 ص - 6 م') }}" 
                                           placeholder="الأحد إلى الخميس، 9 ص - 6 م">
                                </div>
                                @error('support_working_hours')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-check form-switch mt-1">
                                    <input class="form-check-input" type="checkbox" role="switch" id="support_chat_enabled" name="support_chat_enabled" value="1" @checked(old('support_chat_enabled', $settings->support_chat_enabled ?? true))>
                                    <label class="form-check-label small fw-semibold text-dark" for="support_chat_enabled">
                                        إتاحة خيار "محادثة مع الدعم" في التطبيق
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Column 2: Official Stamp --}}
            <div class="col-lg-5">
                <div class="settings-card h-100 d-flex flex-column">
                    <div class="settings-card-header d-flex align-items-center gap-3">
                        <span class="form-section-icon bg-white text-rounder-orange border" style="color:var(--orange, #fb822f) !important;">
                            <i class="fa-solid fa-stamp"></i>
                        </span>
                        <div>
                            <h2 class="h6 fw-bold mb-0 text-dark">{{ __('Official stamp') }}</h2>
                            <small class="text-secondary">{{ __('إرفاق صورة الختم الرسمي لاعتماده في المستندات والعقود') }}</small>
                        </div>
                    </div>
                    <div class="p-4 d-flex flex-column flex-grow-1">
                        {{-- Current Stamp Display if Exists --}}
                        @if($settings->stamp_url)
                            <div class="mb-4 text-center p-3 bg-light rounded-3 border" id="currentStampContainer">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        <i class="fa-solid fa-circle-check me-1"></i> {{ __('الختم المعتمد حالياً') }}
                                    </span>
                                    <div class="form-check form-check-inline m-0">
                                        <input class="form-check-input" type="checkbox" name="remove_stamp" id="remove_stamp" value="1">
                                        <label class="form-check-label small text-danger fw-semibold" for="remove_stamp">
                                            <i class="fa-solid fa-trash-can me-1"></i> {{ __('Remove current stamp') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="stamp-preview-box my-2">
                                    <img src="{{ $settings->stamp_url }}" alt="{{ __('Official stamp') }}" class="stamp-preview-img" id="currentStampImg">
                                </div>
                                <div>
                                    <a href="{{ $settings->stamp_url }}" target="_blank" class="btn btn-sm btn-link text-decoration-none text-secondary">
                                        <i class="fa-solid fa-up-right-from-square me-1"></i> {{ __('عرض بالحجم الكامل') }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        {{-- New Upload Area with Live Preview --}}
                        <div class="stamp-dropzone mb-3" id="stampDropzone" onclick="document.getElementById('stamp_image').click();">
                            <input type="file" 
                                   name="stamp_image" 
                                   id="stamp_image" 
                                   class="d-none" 
                                   accept="image/png,image/jpeg,image/webp,image/svg+xml">

                            <div id="uploadPrompt" class="{{ $settings->stamp_url ? '' : '' }}">
                                <div class="mb-2 text-rounder-orange" style="font-size: 2.2rem; color: var(--orange, #fb822f);">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <h6 class="fw-bold mb-1 text-dark">{{ __('Upload stamp image') }}</h6>
                                <p class="text-secondary small mb-2">{{ __('اضغط لاختيار صورة الختم أو اسحب الملف هنا') }}</p>
                                <span class="badge bg-white text-secondary border px-2 py-1 small">
                                    PNG, JPG, WEBP, SVG (Max: 4MB)
                                </span>
                            </div>

                            <div id="newStampPreviewContainer" class="d-none mt-2">
                                <div class="stamp-preview-box my-2">
                                    <img src="" alt="{{ __('Preview') }}" class="stamp-preview-img" id="newStampPreviewImg">
                                </div>
                                <div class="small fw-semibold text-dark mt-1" id="newStampFileName"></div>
                                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="cancelNewStampBtn" onclick="event.stopPropagation(); cancelNewStamp();">
                                    <i class="fa-solid fa-xmark me-1"></i> {{ __('إلغاء التغيير') }}
                                </button>
                            </div>
                        </div>

                        @error('stamp_image')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="alert alert-light border small text-secondary mt-auto mb-0" style="background: #fbfdfc;">
                            <i class="fa-solid fa-circle-info text-primary me-1"></i>
                            {{ __('ملاحظة: يُفضّل رفع الختم بخلفية شفافة (PNG أو SVG) وبدقة عالية لضمان ظهوره بشكل احترافي على العقود والمطبوعات الرسمية.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Actions Bar --}}
        <div class="settings-card mt-4 p-3 bg-white d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="small text-secondary">
                <i class="fa-solid fa-shield-halved text-success me-1"></i>
                {{ __('يتم تشفير وحفظ البيانات في قاعدة البيانات وتحديث الختم تلقائياً فور الحفظ.') }}
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.company-settings.edit') }}" class="btn btn-outline-secondary px-3" style="border-radius: 9px;">
                    {{ __('Cancel') }}
                </a>
                <button type="submit" class="btn text-white px-4 fw-semibold d-inline-flex align-items-center gap-2" style="background: #02333E; border-radius: 9px;">
                    <i class="fa-solid fa-check"></i>
                    <span>{{ __('Save changes') }}</span>
                </button>
            </div>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('stamp_image');
    const dropzone = document.getElementById('stampDropzone');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const previewContainer = document.getElementById('newStampPreviewContainer');
    const previewImg = document.getElementById('newStampPreviewImg');
    const fileNameText = document.getElementById('newStampFileName');
    const removeStampCheckbox = document.getElementById('remove_stamp');

    if (!fileInput) return;

    fileInput.addEventListener('change', function () {
        handleFiles(this.files);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            handleFiles(files);
        }
    });

    function handleFiles(files) {
        if (!files || files.length === 0) return;
        const file = files[0];

        if (!file.type.match('image.*')) {
            alert('يرجى اختيار ملف صورة صالح (PNG, JPG, WEBP, SVG).');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            fileNameText.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            uploadPrompt.classList.add('d-none');
            previewContainer.classList.remove('d-none');

            // إلغاء تحديد خيار الحذف إن كان محدداً عند اختيار صورة جديدة
            if (removeStampCheckbox) {
                removeStampCheckbox.checked = false;
            }
        };
        reader.readAsDataURL(file);
    }

    window.cancelNewStamp = function () {
        fileInput.value = '';
        previewImg.src = '';
        fileNameText.textContent = '';
        previewContainer.classList.add('d-none');
        uploadPrompt.classList.remove('d-none');
    };
});
</script>
@endsection

