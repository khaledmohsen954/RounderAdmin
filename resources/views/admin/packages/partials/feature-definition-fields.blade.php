<div class="row g-2">
<div class="col-md-3"><label class="form-label">{{ __('Feature code') }}</label><input name="code" class="form-control" value="{{ $feature?->code }}" required maxlength="60" pattern="[A-Za-z0-9_-]+"></div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-3"><label class="form-label">{{ __('Feature name') }} · {{ $label }}</label><input name="name_{{ $locale }}" class="form-control" value="{{ $feature?->{'name_'.$locale} }}" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" required maxlength="160"></div>
@endforeach
<div class="col-md-3"><label class="form-label">{{ __('Display order') }}</label><input name="sort_order" class="form-control" type="number" min="0" max="65535" value="{{ $feature?->sort_order ?? 0 }}" required></div>
<div class="col-md-3"><label class="form-label">{{ __('Status') }}</label><select name="is_active" class="form-select"><option value="1" @selected($feature?->is_active ?? true)>{{ __('Active') }}</option><option value="0" @selected($feature && !$feature->is_active)>{{ __('Inactive') }}</option></select></div>
<div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary">{{ __('Save comparison feature') }}</button></div>
</div>
