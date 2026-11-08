{{-- يبقى textarea متاحاً عند تعطيل JavaScript؛ الخادم هو المسؤول عن تنقية HTML. --}}
@php
    $defaultContractHtml = trim(view('admin.providers.partials.contract-template-content', [
        'provider' => $provider ?? null,
        'contract' => $contract ?? null,
        'companySettings' => $companySettings ?? \App\Modules\Settings\Models\CompanySetting::current(),
    ])->render());
    $initialTerms = old('terms', $terms ?? '');
    if (trim((string) $initialTerms) === '') {
        $initialTerms = $defaultContractHtml;
    }
    $existingProviderSealUrl = null;
    if (!empty($provider?->id) && !empty($provider?->company_seal)) {
        $sealExt = strtolower(pathinfo($provider->company_seal, PATHINFO_EXTENSION));
        if (in_array($sealExt, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $existingProviderSealUrl = route('admin.providers.documents.show', [$provider->id, 'company_seal']);
        }
    }
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
    <label for="terms" class="form-label fw-bold mb-0 d-flex align-items-center gap-2" id="terms-label">
        <i class="fa-solid fa-file-signature text-rounder-orange"></i>
        <span>{{ __('Contract terms') }} (عقد تقديم خدمات الصيانة — راوندر)</span>
    </label>
    <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" id="reset-contract-template-btn" title="إعادة تحميل القالب الرسمي الكامل لعقد راوندر">
        <i class="fa-solid fa-rotate-left"></i>
        <span>إعادة ضبط قالب العقد</span>
    </button>
</div>

<div class="small text-secondary mb-2">
    <i class="fa-solid fa-circle-info text-primary me-1"></i>
    يتم تعبئة بيانات <strong>مزوّد الخدمة (الطرف الثاني)</strong> ونسب العمولة وتاريخ التوقيع تلقائياً من الحقول، كما يمكنك التعديل المباشر على أي بند داخل العقد.
</div>

<textarea id="terms" class="form-control font-monospace" name="terms" maxlength="250000" rows="14" dir="rtl">{{ $initialTerms }}</textarea>

<template id="default-rounder-contract-template">{!! $defaultContractHtml !!}</template>

<div id="terms-toolbar" class="btn-toolbar flex-wrap align-items-center gap-1 mb-2 p-2 bg-light border rounded-3" role="toolbar" aria-label="{{ __('Text formatting') }}" hidden>
    <span class="small text-muted fw-semibold px-2"><i class="fa-solid fa-pen-nib me-1"></i>تنسيق النص:</span>
    @foreach(['bold'=>'Bold','italic'=>'Italic','underline'=>'Underline','insertUnorderedList'=>'Bullet list','insertOrderedList'=>'Numbered list'] as $command=>$label)
        <button type="button" class="btn btn-sm btn-outline-secondary bg-white" data-command="{{ $command }}">{{ __($label) }}</button>
    @endforeach
    <button type="button" class="btn btn-sm btn-outline-secondary bg-white" data-command="formatBlock" data-value="h2">{{ __('Heading') }}</button>
    <button type="button" class="btn btn-sm btn-outline-secondary bg-white" data-command="formatBlock" data-value="p">{{ __('Paragraph') }}</button>
    <button type="button" class="btn btn-sm btn-outline-secondary bg-white" data-command="removeFormat">{{ __('Clear formatting') }}</button>
</div>

<div id="terms-editor" class="form-control p-2 p-lg-3" style="min-height: 420px; max-height: 680px; overflow-y: auto; background: #f4f7f6; border-radius: 14px;" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="terms-label" hidden></div>
<div class="form-text">{{ __('Contract formatting is saved with the provider.') }}</div>

@push('scripts')
<script>
(() => {
    const source = document.getElementById('terms');
    const editor = document.getElementById('terms-editor');
    const toolbar = document.getElementById('terms-toolbar');
    const defaultTpl = document.getElementById('default-rounder-contract-template');
    const syncBtn = document.getElementById('sync-contract-fields-btn');
    const resetBtn = document.getElementById('reset-contract-template-btn');
    const toggleRawBtn = document.getElementById('toggle-raw-textarea-btn');

    if (!source || !editor || !toolbar) return;

    const allowed = new Set([
        'P','DIV','SPAN','H1','H2','H3','H4','H5','H6',
        'STRONG','B','EM','I','U','UL','OL','LI','BLOCKQUOTE',
        'BR','HR','TABLE','THEAD','TBODY','TR','TH','TD','IMG',
        'SECTION','HEADER','FOOTER'
    ]);
    const blocked = new Set(['SCRIPT','STYLE','IFRAME','OBJECT','EMBED','SVG','MATH','TEMPLATE']);
    const allowedAttrs = ['class','style','dir','data-contract-field','colspan','rowspan','width','align','valign','src','alt'];

    function copySafe(node, target) {
        for (const child of node.childNodes) {
            if (child.nodeType === Node.TEXT_NODE) {
                target.append(document.createTextNode(child.textContent));
            } else if (child.nodeType === Node.ELEMENT_NODE && !blocked.has(child.tagName)) {
                if (allowed.has(child.tagName)) {
                    const element = document.createElement(child.tagName.toLowerCase());
                    for (const attr of allowedAttrs) {
                        if (child.hasAttribute(attr)) {
                            element.setAttribute(attr, child.getAttribute(attr));
                        }
                    }
                    copySafe(child, element);
                    target.append(element);
                } else {
                    copySafe(child, target);
                }
            }
        }
    }

    function renderSourceToEditor() {
        editor.innerHTML = '';
        const parsed = new DOMParser().parseFromString(source.value, 'text/html');
        copySafe(parsed.body, editor);
    }

    renderSourceToEditor();
    source.hidden = true;
    editor.hidden = false;
    toolbar.hidden = false;

    const sync = () => {
        source.value = editor.innerHTML;
    };

    const arabicDays = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السabt'];

    function formatArabicDateInfo(dateStr) {
        if (!dateStr) return { formatted: '........................', dayName: 'الأربعاء' };
        const parts = dateStr.split('-');
        if (parts.length !== 3) return { formatted: dateStr, dayName: 'الأربعاء' };
        const [y, m, d] = parts;
        const dt = new Date(Number(y), Number(m) - 1, Number(d));
        const dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        const dayName = !isNaN(dt.getTime()) ? dayNames[dt.getDay()] : 'الأربعاء';
        return {
            formatted: `${d}/${m}/${y}م`,
            dayName
        };
    }

    function syncFormFieldsToContract() {
        const form = source.form;
        if (!form) return;

        const getVal = (name) => {
            const el = form.querySelector(`[name="${name}"]`);
            return el ? el.value.trim() : '';
        };

        const companyName = getVal('company_name');
        const tradeName = getVal('trade_name');
        const companyDisplay = (tradeName && companyName && tradeName !== companyName)
            ? `${companyName} (${tradeName})`
            : (companyName || tradeName || '........................................');

        const cr = getVal('commercial_registration') || '........................';
        const legalRep = getVal('legal_representative') || getVal('name') || '........................';
        const legalRepId = getVal('legal_representative_id') || '........................';
        const phone = getVal('phone') || '........................';
        const email = getVal('email') || '........................';
        const serviceComm = getVal('service_commission_percent') || '15';
        const emergencyComm = getVal('emergency_commission_percent') || '20';
        const startsOn = getVal('starts_on');
        const dateInfo = formatArabicDateInfo(startsOn);

        const fieldMap = {
            company_display: companyDisplay,
            commercial_registration: cr,
            legal_representative: legalRep,
            legal_representative_id: legalRepId,
            phone: phone,
            email: email,
            service_commission_percent: serviceComm,
            emergency_commission_percent: emergencyComm,
            starts_on_formatted: dateInfo.formatted,
            day_name: dateInfo.dayName,
        };

        let updated = false;
        Object.entries(fieldMap).forEach(([key, val]) => {
            editor.querySelectorAll(`[data-contract-field="${key}"]`).forEach(el => {
                if (el.textContent !== val) {
                    el.textContent = val;
                    updated = true;
                }
            });
        });

        if (updated) {
            sync();
        }
    }

    // ربط الحقول بتحديث العقد تلقائياً فور الكتابة
    if (source.form) {
        const watchedFields = [
            'company_name', 'trade_name', 'name',
            'commercial_registration', 'legal_representative', 'legal_representative_id',
            'phone', 'email', 'starts_on',
            'service_commission_percent', 'emergency_commission_percent'
        ];
        watchedFields.forEach(name => {
            const input = source.form.querySelector(`[name="${name}"]`);
            if (input) {
                input.addEventListener('input', syncFormFieldsToContract);
                input.addEventListener('change', syncFormFieldsToContract);
            }
        });
    }

    const contractTabBtn = document.getElementById('tab-contract-btn');
    if (contractTabBtn) {
        contractTabBtn.addEventListener('click', syncFormFieldsToContract);
        contractTabBtn.addEventListener('shown.bs.tab', syncFormFieldsToContract);
    }

    if (resetBtn && defaultTpl) {
        resetBtn.addEventListener('click', () => {
            source.value = defaultTpl.innerHTML.trim();
            renderSourceToEditor();
            syncFormFieldsToContract();
            sync();
        });
    }

    const existingSealUrl = @json($existingProviderSealUrl);
    let previewSealObjectUrl = null;

    function renderSecondPartySeal(url, isTempBlob = false) {
        const sealContainers = editor.querySelectorAll('[data-contract-field="second_party_seal"]');
        sealContainers.forEach(container => {
            container.innerHTML = '';
            if (url) {
                const img = document.createElement('img');
                img.src = url;
                img.alt = 'ختم الطرف الثاني';
                img.style.maxHeight = '95px';
                img.style.maxWidth = '160px';
                if (isTempBlob) {
                    img.setAttribute('data-temp-seal', '1');
                }
                container.appendChild(img);
            }
        });
        if (!isTempBlob) {
            sync();
        }
    }

    if (existingSealUrl) {
        const hasSealImg = editor.querySelector('[data-contract-field="second_party_seal"] img');
        if (!hasSealImg) {
            renderSecondPartySeal(existingSealUrl, false);
        }
    }

    if (source.form) {
        const sealInput = source.form.querySelector('input[name="company_seal"]');
        if (sealInput) {
            sealInput.addEventListener('change', function () {
                if (previewSealObjectUrl) {
                    URL.revokeObjectURL(previewSealObjectUrl);
                    previewSealObjectUrl = null;
                }
                const file = this.files && this.files[0];
                if (file && file.type && file.type.startsWith('image/')) {
                    previewSealObjectUrl = URL.createObjectURL(file);
                    renderSecondPartySeal(previewSealObjectUrl, true);
                } else {
                    renderSecondPartySeal(existingSealUrl, false);
                }
            });
        }
    }

    // مزامنة أولية عند التحميل
    syncFormFieldsToContract();

    editor.addEventListener('input', sync);
    source.addEventListener('input', () => {
        if (!source.hidden) {
            renderSourceToEditor();
        }
    });

    toolbar.addEventListener('mousedown', event => event.preventDefault());
    toolbar.addEventListener('click', event => {
        const button = event.target.closest('[data-command]');
        if (!button) return;
        editor.focus();
        document.execCommand(button.dataset.command, false, button.dataset.value || null);
        sync();
    });

    editor.addEventListener('paste', event => {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        sync();
    });
    editor.addEventListener('drop', event => event.preventDefault());
    if (source.form) {
        source.form.addEventListener('submit', () => {
            if (!source.hidden) {
                return;
            }
            // إزالة رابط المعاينة المؤقت (blob:) قبل الحفظ ليقوم السيرفر بحفظ الملف الرسمي وربطه تلقائياً
            editor.querySelectorAll('img[data-temp-seal="1"]').forEach(img => img.remove());
            sync();
        });
    }
})();
</script>
@endpush
