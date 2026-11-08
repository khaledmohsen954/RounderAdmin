@extends('layouts.admin')
@section('title', ($set->exists ? __('Edit permission set') : __('Add permission set')) . ' · Rounder')
@section('body')
<main class="container py-4">
    {{-- Header & Breadcrumb --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-secondary mb-1">
                <a href="{{ route('admin.permissions.index') }}" class="text-decoration-none text-secondary">
                    <i class="fa-solid fa-arrow-right me-1"></i>
                    {{ __('Permissions') }}
                </a>
                <span class="mx-1 text-muted">/</span>
                <span class="text-dark">{{ $set->exists ? ($set->name_ar ?? $set->name_en) : __('Add permission set') }}</span>
            </div>
            <h1 class="h3 fw-bold mb-0 d-flex align-items-center gap-2">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 36px; height: 36px; background: #fff3eb; color: var(--orange, #fb822f);">
                    <i class="fa-solid fa-shield-halved" style="font-size: 1rem;"></i>
                </span>
                <span>{{ $set->exists ? __('Edit permission set') : __('Add permission set') }}</span>
                @if($set->exists)
                    <span class="badge bg-light text-secondary border font-monospace fs-6">#{{ $set->id }}</span>
                @endif
            </h1>
        </div>
        <div>
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2" style="border-radius: 10px;">
                <i class="fa-solid fa-arrow-right"></i>
                <span>{{ __('Back') }}</span>
            </a>
        </div>
    </div>

    @include('admin.partials.feedback')

    {{-- Form Panel --}}
    <form class="panel p-3 p-md-4 shadow-sm" method="post" action="{{ $set->exists ? route('admin.permissions.update', $set->id) : route('admin.permissions.store') }}" style="border-top: 3px solid var(--orange, #fb822f) !important; border-radius: 14px;">
        @csrf
        @if($set->exists) @method('PUT') @endif

        {{-- Names Row --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary" for="name-ar">
                    <i class="fa-solid fa-language me-1 text-rounder-orange"></i>
                    {{ __('Name') }} · العربية <span class="text-danger">*</span>
                </label>
                <input id="name-ar" name="name_ar" class="form-control" maxlength="120" minlength="2" required value="{{ old('name_ar', $set->name_ar) }}" placeholder="مثال: مدير العمليات" style="border-radius: 10px;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold small text-secondary" for="name-en">
                    <i class="fa-solid fa-language me-1 text-rounder-orange"></i>
                    {{ __('Name') }} · English <span class="text-danger">*</span>
                </label>
                <input id="name-en" name="name_en" class="form-control" maxlength="120" minlength="2" required value="{{ old('name_en', $set->name_en) }}" placeholder="e.g. Operations Manager" style="border-radius: 10px; text-align: left;" dir="ltr">
            </div>
        </div>

        {{-- Permissions Matrix --}}
        <div class="mb-3">
            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                <i class="fa-solid fa-key text-rounder-orange"></i>
                <span>{{ __('Permissions') }}</span>
            </h5>
            <div class="table-responsive rounded-3 border" style="border-color: #e2ece7 !important;">
                <table class="table table-hover align-middle mb-0" style="min-width: 720px;">
                    <thead class="bg-light-subtle">
                        <tr class="text-secondary small fw-bold">
                            <th>{{ __('Section') }}</th>
                            <th style="width: 140px;">{{ __('Select all') }}</th>
                            @foreach($actions as $action)
                                <th class="text-center" style="width: 110px;">{{ __(ucfirst($action->value)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sections as $section)
                            <tr>
                                <td class="fw-bold text-dark">
                                    <i class="fa-regular fa-folder me-2 text-primary"></i>
                                    {{ $section->label() }}
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input row-toggle" style="cursor: pointer;" aria-label="{{ __('Select all').' '.$section->label() }}">
                                        <label class="form-check-label small text-secondary" style="cursor: pointer;">{{ __('Select all') }}</label>
                                    </div>
                                </td>
                                @foreach($actions as $action)
                                    @php($key = $section->value.'.'.$action->value)
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input permission-checkbox" name="permissions[]" value="{{ $key }}"
                                            style="cursor: pointer; width: 1.15em; height: 1.15em;"
                                            aria-label="{{ $section->label().' · '.__(ucfirst($action->value)) }}"
                                            @checked(in_array($key, old('permissions', session()->hasOldInput() ? [] : ($set->permissions ?? [])), true))>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Submit Footer --}}
        <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-light px-4" style="border-radius: 10px;">
                {{ __('Cancel') }}
            </a>
            <button class="btn btn-brand px-4" style="border-radius: 10px;">
                <i class="fa-solid fa-check me-1"></i>
                {{ __('Save') }}
            </button>
        </div>
    </form>
</main>
@push('scripts')
<script>
document.querySelectorAll('.row-toggle').forEach(toggle => {
    const boxes = [...toggle.closest('tr').querySelectorAll('.permission-checkbox')];
    const sync = () => { 
        toggle.checked = boxes.length > 0 && boxes.every(box => box.checked); 
        toggle.indeterminate = boxes.some(box => box.checked) && !toggle.checked; 
    };
    toggle.addEventListener('change', () => {
        boxes.forEach(box => box.checked = toggle.checked);
    });
    boxes.forEach(box => box.addEventListener('change', sync)); 
    sync();
});
</script>
@endpush
@endsection
