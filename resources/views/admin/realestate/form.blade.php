@extends('layouts.admin')
@section('title', __('Homes').' · Rounder')
@section('body')
<main class="container py-4"><h1 class="h3 mb-4">{{ __('Homes') }}</h1>
@include('admin.partials.feedback')
@adminCan('admin.realestate.index')<a class="btn btn-outline-secondary mb-3" href="{{ route('admin.realestate.index') }}">{{ __('Back') }}</a>@endadminCan
<form method="post" action="{{ $home->exists ? route('admin.realestate.update',$home->id) : route('admin.realestate.store') }}" class="panel p-4">
@csrf @if($home->exists) @method('PUT') @endif
<h2 class="h5 mb-3">{{ $home->exists ? __('Edit home') : __('Add home') }}</h2>
<div class="row g-3">
@foreach(['name'=>'Home name','property_code'=>'Property code','address'=>'Address'] as $field=>$label)
<div class="col-md-{{ $field==='address' ? 12 : 6 }}"><label class="form-label" for="home-{{ $field }}">{{ __($label) }}</label><input class="form-control" id="home-{{ $field }}" name="{{ $field }}" value="{{ old($field,$home->$field) }}" required maxlength="{{ $field==='address' ? 255 : ($field==='name' ? 120 : 60) }}"></div>
@endforeach
<div class="col-md-6"><label for="city-search" class="form-label">{{ __('Search Saudi cities') }}</label><input id="city-search" type="search" class="form-control mb-2">
<label for="home-city" class="form-label">{{ __('City') }}</label><select id="home-city" name="city_id" class="form-select" required><option value="">{{ __('Choose city') }}</option>
@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id',$home->city_id)==$city->id)>{{ $city->translations->firstWhere('locale',app()->getLocale())?->name ?? $city->code }} · {{ $city->code }}</option>@endforeach</select></div>
<div class="col-md-2"><label for="floors_count" class="form-label">{{ __('Floors count') }}</label><input id="floors_count" name="floors_count" type="number" min="1" max="200" class="form-control" value="{{ old('floors_count',$home->floors_count ?? 1) }}" required></div>
<div class="col-md-2"><label for="health_percent" class="form-label">{{ __('Property health') }}</label><input id="health_percent" name="health_percent" type="number" min="0" max="100" class="form-control" value="{{ old('health_percent',$home->health_percent ?? 100) }}" required></div>
<div class="col-md-2"><label for="association_balance" class="form-label">{{ __('Association balance') }}</label><input id="association_balance" name="association_balance" type="number" min="0" step=".01" class="form-control" value="{{ old('association_balance',$home->association_balance ?? 0) }}" required></div>
</div>
<div class="d-flex justify-content-between align-items-center mt-4 mb-3"><h2 class="h5 mb-0">{{ __('Apartment details') }}</h2><button type="button" id="add-unit" class="btn btn-outline-primary">{{ __('Add apartment') }}</button></div>
<p class="text-secondary">{{ __('Floor numbers start at 1 and cannot exceed the building floors count.') }}</p>
<div class="table-responsive"><table class="table"><thead><tr><th>{{ __('Apartment number') }}</th><th>{{ __('Floor') }}</th><th>{{ __('Area (m²)') }}</th><th>{{ __('Rooms count') }}</th><th>{{ __('Actions') }}</th></tr></thead>
<tbody id="unit-rows">
@foreach(old('units',$home->exists && $home->units->isNotEmpty() ? $home->units->toArray() : [['unit_number'=>'','floor_number'=>1,'area_sqm'=>'','rooms_count'=>1]]) as $i=>$unit)
<tr><td><input type="hidden" data-field="id" name="units[{{ $i }}][id]" value="{{ $unit['id'] ?? '' }}"><input aria-label="{{ __('Apartment number') }}" class="form-control" data-field="unit_number" name="units[{{ $i }}][unit_number]" value="{{ $unit['unit_number'] }}" maxlength="40" required></td>
<td><input aria-label="{{ __('Floor') }}" class="form-control" type="number" data-field="floor_number" name="units[{{ $i }}][floor_number]" value="{{ $unit['floor_number'] }}" min="1" max="200" required></td>
<td><input aria-label="{{ __('Area (m²)') }}" class="form-control" type="number" data-field="area_sqm" name="units[{{ $i }}][area_sqm]" value="{{ $unit['area_sqm'] }}" min=".01" max="99999999.99" step=".01" required></td>
<td><input aria-label="{{ __('Rooms count') }}" class="form-control" type="number" data-field="rooms_count" name="units[{{ $i }}][rooms_count]" value="{{ $unit['rooms_count'] }}" min="1" max="100" required></td>
<td><button type="button" class="btn btn-outline-danger remove-unit">{{ __('Remove') }}</button></td></tr>
@endforeach
</tbody></table></div><button class="btn btn-primary mt-3">{{ __('Save') }}</button>
</form>
<div class="modal fade" id="remove-unit-confirmation" tabindex="-1" aria-labelledby="remove-unit-title"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="remove-unit-title">{{ __('Confirm deletion') }}</h2></div><div class="modal-body">{{ __('Are you sure you want to delete this record?') }}</div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('No') }}</button><button type="button" class="btn btn-danger" id="remove-unit-yes">{{ __('Yes') }}</button></div></div></div></div>
@push('scripts')
<script>
(() => {
    const rows = document.getElementById('unit-rows');
    const template = rows.firstElementChild.cloneNode(true);
    const renumber = () => [...rows.children].forEach((row,index) => row.querySelectorAll('[data-field]').forEach(input => input.name='units['+index+']['+input.dataset.field+']'));
    document.getElementById('add-unit').addEventListener('click',()=> {
        if (rows.children.length>=500) return;
        const row=template.cloneNode(true);
        row.querySelectorAll('input').forEach(input=>input.value=['floor_number','rooms_count'].includes(input.dataset.field)?'1':'');
        rows.append(row); renumber();
    });
    let pending=null;
    const modal=bootstrap.Modal.getOrCreateInstance(document.getElementById('remove-unit-confirmation'));
    rows.addEventListener('click',event=>{
        if (!event.target.closest('.remove-unit') || rows.children.length<=1) return;
        pending=event.target.closest('tr'); modal.show();
    });
    document.getElementById('remove-unit-yes').addEventListener('click',()=>{ if(pending){pending.remove();pending=null;renumber();} modal.hide(); });
    document.getElementById('city-search').addEventListener('input',function(){
        const query=this.value.trim().toLocaleLowerCase();
        for(const option of document.getElementById('home-city').options) option.hidden=option.value!==''&&!option.text.toLocaleLowerCase().includes(query);
    });
})();
</script>
@endpush
</main>
@endsection
