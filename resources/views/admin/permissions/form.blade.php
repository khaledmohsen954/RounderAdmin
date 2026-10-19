@extends('layouts.admin')
@section('title', __('Permissions').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Permissions') }}</h1>
@include('admin.partials.feedback')
@adminCan('admin.permissions.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.permissions.index') }}">{{ __('Back') }}</a>@endadminCan
<form class="panel p-4" method="post" action="{{ $set->exists ? route('admin.permissions.update',$set->id) : route('admin.permissions.store') }}">
@csrf @if($set->exists) @method('PUT') @endif
<div class="row g-3 mb-4">
@foreach(['ar'=>'Arabic','en'=>'English'] as $locale=>$label)
<div class="col-md-6"><label class="form-label" for="name-{{ $locale }}">{{ __('Name') }} · {{ __($label) }}</label>
<input id="name-{{ $locale }}" name="name_{{ $locale }}" class="form-control" maxlength="120" minlength="2" required value="{{ old('name_'.$locale,$set->{'name_'.$locale}) }}"></div>
@endforeach
</div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>{{ __('Section') }}</th><th>{{ __('Select all') }}</th>
@foreach($actions as $action)<th>{{ __(ucfirst($action->value)) }}</th>@endforeach
</tr></thead><tbody>
@foreach($sections as $section)<tr><th>{{ $section->label() }}</th>
<td><input type="checkbox" class="form-check-input row-toggle" aria-label="{{ __('Select all').' '.$section->label() }}"></td>
@foreach($actions as $action)
@php($key=$section->value.'.'.$action->value)
<td><input type="checkbox" class="form-check-input permission-checkbox" name="permissions[]" value="{{ $key }}"
aria-label="{{ $section->label().' · '.__(ucfirst($action->value)) }}"
@checked(in_array($key,old('permissions',session()->hasOldInput() ? [] : ($set->permissions ?? [])),true))></td>
@endforeach
</tr>@endforeach
</tbody></table></div>
<button class="btn btn-primary mt-3">{{ __('Save') }}</button>
</form>
@push('scripts')
<script>
document.querySelectorAll('.row-toggle').forEach(toggle => {
    const boxes = [...toggle.closest('tr').querySelectorAll('.permission-checkbox')];
    const sync = () => { toggle.checked = boxes.every(box=>box.checked); toggle.indeterminate = boxes.some(box=>box.checked) && !toggle.checked; };
    toggle.addEventListener('change',()=>boxes.forEach(box=>box.checked=toggle.checked));
    boxes.forEach(box=>box.addEventListener('change',sync)); sync();
});
</script>
@endpush
</main>
@endsection
