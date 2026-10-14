<?php
namespace App\Modules\Catalog\Requests;

use App\Modules\Catalog\Enums\DiscountType;
use Illuminate\Validation\Rule;

/** قواعد مشتركة لصور وأسعار الكتالوج؛ حدود الخصم مقابل السعر تحققها خدمة التسعير أيضاً. */
trait CatalogPricingRules
{
    /**
     * @return array<string, mixed>
     */
    protected function pricingRules(): array
    {
        return [
            'original_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'discount_type' => ['sometimes', 'required', Rule::enum(DiscountType::class)],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
        ];
    }
}
