<?php
namespace App\Modules\Catalog\Services;

use App\Modules\Catalog\Enums\DiscountType;
use Illuminate\Validation\ValidationException;

/** حساب السعر النهائي مركزياً بوحدات نقدية صحيحة لمنع أخطاء الكسور العائمة. */
class CatalogPricingService
{
    /**
     * السعر المرسل هو سعر العميل قبل الخصم؛ السعر النهائي لا يقبل من العميل.
     * @param array<string, mixed> $input
     * @param string $priceField
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function attributes(array $input, string $priceField): array
    {
        $before = $this->minorUnits($input[$priceField], $priceField);
        $value = $this->minorUnits($input['discount_value'] ?? 0, 'discount_value');
        $type = DiscountType::tryFrom($input['discount_type'] ?? DiscountType::None->value);
        if (!$type || ($type === DiscountType::Percentage && $value > 10000)
            || ($type === DiscountType::Fixed && $value > $before)
            || ($type === DiscountType::None && $value !== 0)) {
            throw ValidationException::withMessages(['discount_value' => __('Invalid discount for this price.')]);
        }
        // النسبة مخزنة بجزئين عشريين: 12.50% تساوي 1250 نقطة أساس.
        $discount = match ($type) {
            DiscountType::None => 0,
            DiscountType::Fixed => $value,
            DiscountType::Percentage => intdiv($before * $value + 5000, 10000),
        };
        return [
            'original_price' => isset($input['original_price']) ? $this->decimal($this->minorUnits($input['original_price'], 'original_price')) : null,
            'price_before_discount' => $this->decimal($before),
            'discount_type' => $type->value,
            'discount_value' => $this->decimal($value),
            $priceField => $this->decimal($before - $discount),
        ];
    }

    /**
     * التحويل من نص عشري؛ نرفض الأرقام السالبة أو الدقة الزائدة بدلاً من التقريب الضمني.
     * @param mixed $amount
     * @param string $field
     * @return int
     * @throws ValidationException
     */
    private function minorUnits(mixed $amount, string $field): int
    {
        if (!preg_match('/^\d{1,10}(?:\.\d{1,2})?$/D', (string) $amount)) {
            throw ValidationException::withMessages([$field => __('Use a non-negative price with at most two decimals.')]);
        }
        $parts = explode('.', (string) $amount, 2);
        return ((int) $parts[0] * 100) + (int) str_pad($parts[1] ?? '', 2, '0');
    }

    /**
     * @param int $amount
     * @return string
     */
    private function decimal(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
