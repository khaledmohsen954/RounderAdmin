<?php
namespace App\Actions;
/** حساب نسبة مالية مرة واحدة بالقيم الصغرى لتجنب تراكم أخطاء الكسور. */
final class CalculatePercentageAmount
{
    private const MINOR_UNITS = 100;
    private const RATE_SCALE = 10000;

    /**
     * يقرب نصف الهللة إلى أعلى، ويعيد مبلغاً عشرياً صالحاً لحقل DECIMAL.
     * @param float $gross
     * @param float $percent
     * @return string
     */
    public function execute(float $gross, float $percent): string
    {
        $minor = (int) round($gross * self::MINOR_UNITS);
        $rate = (int) round($percent * self::MINOR_UNITS);
        $commission = intdiv($minor * $rate + intdiv(self::RATE_SCALE, 2), self::RATE_SCALE);
        return intdiv($commission, self::MINOR_UNITS).'.'.str_pad(
            (string) ($commission % self::MINOR_UNITS), 2, '0', STR_PAD_LEFT);
    }
}
