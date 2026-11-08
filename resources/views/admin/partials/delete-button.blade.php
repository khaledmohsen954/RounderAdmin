{{-- زر مستقل لا يرسل DELETE قبل تأكيد المستخدم داخل النافذة المشتركة. --}}
<form method="post" action="{{ $action }}" class="d-inline-flex m-0 delete-record-form">
@csrf @method('DELETE')
<button type="button" data-confirm-delete class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" title="{{ __('Delete') }}" style="border-radius:8px; font-weight:600; padding:0.35rem 0.75rem; font-size:0.82rem;">
    <i class="fa-solid fa-trash-can"></i>
    <span>{{ __('Delete') }}</span>
</button>
</form>
