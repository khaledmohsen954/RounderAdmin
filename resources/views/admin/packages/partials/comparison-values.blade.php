<fieldset class="mt-4">
<legend class="h5">{{ __('Package comparison features') }}</legend>
<p class="small text-secondary">{{ __('Comparison text describes the package. Bookable service quantities are configured separately below.') }}</p>
@php($savedFeatures = collect(old('features', []))->keyBy('package_feature_id'))
@foreach($featureDefinitions as $definition)
@php($value = $featureValues->get($definition->id))
@php($oldValue = $savedFeatures->get($definition->id))
<div class="border rounded p-3 mb-3">
<strong>{{ $definition->{'name_'.app()->getLocale()} }}</strong>
@if(!$definition->is_active)<span class="badge text-bg-secondary">{{ __('Inactive') }}</span>@endif
<input type="hidden" name="features[{{ $loop->index }}][package_feature_id]" value="{{ $definition->id }}">
<div class="row g-2 mt-1">
<div class="col-md-3"><label class="form-label">{{ __('Availability in package') }}</label><select name="features[{{ $loop->index }}][availability]" class="form-select">
@foreach(\App\Modules\Catalog\Enums\FeatureAvailability::cases() as $availability)
<option value="{{ $availability->value }}" @selected(($oldValue['availability'] ?? $value?->availability?->value ?? 'excluded') === $availability->value)>{{ __('feature.'.$availability->value) }}</option>
@endforeach
</select></div>
@foreach(['ar'=>'العربية','en'=>'English','ur'=>'اردو'] as $locale=>$label)
<div class="col-md-3"><label class="form-label">{{ __('Feature details') }} · {{ $label }}</label><input name="features[{{ $loop->parent->index }}][value_{{ $locale }}]" class="form-control" maxlength="500" dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}" value="{{ $oldValue['value_'.$locale] ?? $value?->{'value_'.$locale} }}"></div>
@endforeach
</div></div>
@endforeach
@if($featureDefinitions->isEmpty())<p class="text-secondary">{{ __('Add comparison definitions from the catalog page first.') }}</p>@endif
</fieldset>
