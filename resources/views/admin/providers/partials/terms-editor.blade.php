{{-- يبقى textarea متاحاً عند تعطيل JavaScript؛ الخادم هو المسؤول عن تنقية HTML. --}}
<label for="terms" class="form-label" id="terms-label">{{ __('Contract terms') }}</label>
<textarea id="terms" class="form-control" name="terms" maxlength="10000" rows="6">{{ old('terms') }}</textarea>
<div id="terms-toolbar" class="btn-toolbar gap-2 mb-2" role="toolbar" aria-label="{{ __('Text formatting') }}" hidden>
@foreach(['bold'=>'Bold','italic'=>'Italic','underline'=>'Underline','insertUnorderedList'=>'Bullet list','insertOrderedList'=>'Numbered list'] as $command=>$label)
<button type="button" class="btn btn-sm btn-outline-secondary" data-command="{{ $command }}">{{ __($label) }}</button>
@endforeach
<button type="button" class="btn btn-sm btn-outline-secondary" data-command="formatBlock" data-value="h2">{{ __('Heading') }}</button>
<button type="button" class="btn btn-sm btn-outline-secondary" data-command="formatBlock" data-value="p">{{ __('Paragraph') }}</button>
<button type="button" class="btn btn-sm btn-outline-secondary" data-command="removeFormat">{{ __('Clear formatting') }}</button>
</div>
<div id="terms-editor" class="form-control" style="min-height:180px" contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="terms-label" hidden></div>
<div class="form-text">{{ __('Contract formatting is saved with the provider.') }}</div>
@push('scripts')
<script>
(() => {
    const source = document.getElementById('terms');
    const editor = document.getElementById('terms-editor');
    const toolbar = document.getElementById('terms-toolbar');
    // لا نضع المدخل الخام داخل الصفحة حتى عند إعادة عرض أخطاء التحقق.
    const allowed = new Set(['P','DIV','H2','H3','STRONG','B','EM','I','U','UL','OL','LI','BLOCKQUOTE','BR']);
    const blocked = new Set(['SCRIPT','STYLE','IFRAME','OBJECT','EMBED','SVG','MATH','TEMPLATE']);
    function copySafe(node, target) {
        for (const child of node.childNodes) {
            if (child.nodeType === Node.TEXT_NODE) target.append(document.createTextNode(child.textContent));
            else if (child.nodeType === Node.ELEMENT_NODE && !blocked.has(child.tagName)) {
                if (allowed.has(child.tagName)) {
                    const element = document.createElement(child.tagName.toLowerCase());
                    copySafe(child, element); target.append(element);
                } else copySafe(child, target);
            }
        }
    }
    const parsed = new DOMParser().parseFromString(source.value, 'text/html');
    copySafe(parsed.body, editor);
    source.hidden = true; editor.hidden = false; toolbar.hidden = false;
    const sync = () => { source.value = editor.innerHTML; };
    editor.addEventListener('input', sync);
    toolbar.addEventListener('mousedown', event => event.preventDefault());
    toolbar.addEventListener('click', event => {
        const button = event.target.closest('[data-command]');
        if (!button) return;
        editor.focus();
        document.execCommand(button.dataset.command, false, button.dataset.value || null);
        sync();
    });
    // لصق النص فقط يمنع دخول تنسيقات وأكواد خارج أدوات المحرر.
    editor.addEventListener('paste', event => {
        event.preventDefault();
        document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        sync();
    });
    editor.addEventListener('drop', event => event.preventDefault());
    source.form.addEventListener('submit', sync);
})();
</script>
@endpush
