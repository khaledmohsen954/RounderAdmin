<div class="col-12 mt-4">
    <div class="p-3 p-lg-4 rounded-3 border bg-white shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-rounder-orange"></i>
                    <span>{{ __('Package comparison features') }}</span>
                </h6>
                <p class="text-secondary small mb-0">{{ __('Comparison text describes the package. Bookable service quantities are configured separately below.') }}</p>
            </div>
            @if($featureDefinitions->isNotEmpty())
                <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill font-monospace">
                    {{ $featureDefinitions->count() }} {{ __('Features') }}
                </span>
            @endif
        </div>

        @php($savedFeatures = collect(old('features', []))->keyBy('package_feature_id'))

        @forelse($featureDefinitions as $definition)
            @php($value = $featureValues->get($definition->id))
            @php($oldValue = $savedFeatures->get($definition->id))
            <div class="p-3 rounded-3 mb-3 border bg-light-subtle">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <strong class="text-dark d-flex align-items-center gap-2">
                        <i class="fa-regular fa-circle-check text-success"></i>
                        <span>{{ $definition->{'name_'.app()->getLocale()} }}</span>
                        <code class="text-secondary small font-monospace">({{ $definition->code }})</code>
                    </strong>
                    @if(!$definition->is_active)
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill small">{{ __('Inactive') }}</span>
                    @endif
                </div>

                <input type="hidden" name="features[{{ $loop->index }}][package_feature_id]" value="{{ $definition->id }}">

                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-secondary">{{ __('Availability in package') }}</label>
                        <select name="features[{{ $loop->index }}][availability]" class="form-select form-select-sm" style="border-radius: 8px;">
                            @foreach(\App\Modules\Catalog\Enums\FeatureAvailability::cases() as $availability)
                                <option value="{{ $availability->value }}" @selected(($oldValue['availability'] ?? $value?->availability?->value ?? 'excluded') === $availability->value)>
                                    {{ __('feature.'.$availability->value) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @foreach(['ar' => ['label' => 'العربية', 'flag' => '🇸🇦'], 'en' => ['label' => 'English', 'flag' => '🇬🇧'], 'ur' => ['label' => 'اردو', 'flag' => '🇵🇰']] as $locale => $meta)
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-secondary">
                                <span class="me-1">{{ $meta['flag'] }}</span> {{ __('Feature details') }} · {{ $meta['label'] }}
                            </label>
                            <input name="features[{{ $loop->parent->index }}][value_{{ $locale }}]"
                                   class="form-control form-control-sm"
                                   maxlength="500"
                                   dir="{{ $locale === 'en' ? 'ltr' : 'rtl' }}"
                                   value="{{ $oldValue['value_'.$locale] ?? $value?->{'value_'.$locale} }}"
                                   placeholder="{{ $meta['label'] }}"
                                   style="border-radius: 8px;">
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-muted">
                <i class="fa-solid fa-sliders fa-2x opacity-50 mb-2"></i>
                <p class="small mb-0">{{ __('Add comparison definitions from the catalog page first.') }}</p>
            </div>
        @endforelse
    </div>
</div>
