{{-- حقول مشتركة؛ الحساب النهائي داخل CatalogPricingService وليس في المتصفح. --}}
<div class="col-md-4">
<label class="form-label">{{ __('Original price (admin reference)') }}</label>
<input name="original_price" type="number" min="0" max="9999999999.99" step=".01" class="form-control" value="{{ old('original_price', $item?->original_price) }}">
</div>
<div class="col-md-4">
<label class="form-label">{{ __('Discount type') }}</label>
<select name="discount_type" class="form-select">
@foreach(\App\Modules\Catalog\Enums\DiscountType::cases() as $type)
<option value="{{ $type->value }}" @selected(old('discount_type', $item?->discount_type?->value ?? 'none') === $type->value)>{{ __('discount.'.$type->value) }}</option>
@endforeach
</select>
</div>
<div class="col-md-4"><label class="form-label">{{ __('Discount value') }}</label>
<input name="discount_value" type="number" min="0" max="9999999999.99" step=".01" class="form-control" value="{{ old('discount_value', $item?->discount_value ?? 0) }}">
</div>
<div class="col-md-6"><label class="form-label">{{ __('Product image') }}</label>
<input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control">
<div class="form-text">{{ __('JPG, PNG or WebP, up to 4 MB.') }}</div>
@if($item?->image_path)
<img class="rounded my-2" width="96" height="96" style="object-fit:cover" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ __('Product image') }}">
<label class="d-block"><input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))> {{ __('Remove image') }}</label>
@endif
</div>
<div class="col-12"><p class="small text-secondary mb-0">{{ __('Final price is calculated on save. Existing orders and subscriptions keep their prices.') }}</p>
@if($item)<strong>{{ __('Current price after discount') }}: {{ number_format($item->{$priceField}, 2) }}</strong>@endif
</div>
