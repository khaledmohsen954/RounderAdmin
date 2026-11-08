@if(session('status'))
<div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="status">
    <i class="fa-solid fa-circle-check fs-5"></i>
    <div class="flex-grow-1">{{ session('status') }}</div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
</div>
@endif
@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
    <div class="d-flex align-items-center gap-2 mb-1">
        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
        <strong>{{ __('Please check the form errors') }}:</strong>
    </div>
    <ul class="mb-0 mt-1 pe-3 ps-3">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
</div>
@endif
