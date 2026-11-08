{{-- حقول التسعير والخصم والصورة المشتركة --}}
<div class="col-12">
    <div class="p-3 rounded-3 bg-light-subtle border">
        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
            <i class="fa-solid fa-tags text-rounder-orange"></i>
            <span>{{ __('Pricing and discounts') }}</span>
        </h6>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">{{ __('Original price (admin reference)') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-receipt"></i>
                    </span>
                    <input name="original_price" type="number" min="0" max="9999999999.99" step=".01" class="form-control border-start-0 font-monospace" value="{{ old('original_price', $item?->original_price) }}" placeholder="0.00" style="border-radius: 10px 0 0 10px;">
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">{{ __('Discount type') }}</label>
                <select name="discount_type" class="form-select" style="border-radius: 10px;">
                    @foreach(\App\Modules\Catalog\Enums\DiscountType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('discount_type', $item?->discount_type?->value ?? 'none') === $type->value)>
                            {{ __('discount.'.$type->value) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label small fw-semibold text-secondary">{{ __('Discount value') }}</label>
                <div class="input-group">
                    <span class="input-group-text bg-white text-secondary border-end-0" style="border-radius: 0 10px 10px 0;">
                        <i class="fa-solid fa-percent"></i>
                    </span>
                    <input name="discount_value" type="number" min="0" max="9999999999.99" step=".01" class="form-control border-start-0 font-monospace" value="{{ old('discount_value', $item?->discount_value ?? 0) }}" style="border-radius: 10px 0 0 10px;">
                </div>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold text-secondary">{{ __('Product image') }}</label>
                <input name="image" type="file" accept="image/jpeg,image/png,image/webp" class="form-control" style="border-radius: 10px;">
                <div class="form-text small text-muted">{{ __('JPG, PNG or WebP, up to 4 MB.') }}</div>
                @if($item?->image_path)
                    <div class="d-flex align-items-center gap-3 mt-2 p-2 border rounded-3 bg-white">
                        <img class="rounded-3 border" width="60" height="60" style="object-fit: cover;" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->image_path) }}" alt="{{ __('Product image') }}">
                        <label class="form-check-label small text-danger fw-semibold cursor-pointer">
                            <input type="checkbox" name="remove_image" value="1" class="form-check-input me-1" @checked(old('remove_image'))>
                            <i class="fa-solid fa-trash-can me-1"></i> {{ __('Remove image') }}
                        </label>
                    </div>
                @endif
            </div>

            <div class="col-12 col-md-6 d-flex align-items-center">
                <div class="p-3 bg-white rounded-3 border w-100">
                    <p class="small text-secondary mb-1">
                        <i class="fa-solid fa-circle-info text-primary me-1"></i>
                        {{ __('Final price is calculated on save. Existing orders and subscriptions keep their prices.') }}
                    </p>
                    @if($item)
                        <div class="fw-bold text-dark mt-2">
                            {{ __('Current price after discount') }}:
                            <span class="text-success font-monospace fs-6">{{ number_format($item->{$priceField}, 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
