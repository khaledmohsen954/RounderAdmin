<?php

namespace App\Core\Support;

class SaudiPhone
{
    public const REGEX = '/^05[0-9]{8}$/';

    /**
     * تنظيف وتوحيد صيغة رقم الهاتف السعودي ليبدأ دائماً بـ 05 ويتكون من 10 أرقام.
     */
    public static function normalize(?string $phone): string
    {
        if ($phone === null) {
            return '';
        }

        $cleaned = preg_replace('/[^\d+]/', '', trim($phone));

        if (str_starts_with($cleaned, '+966')) {
            $cleaned = '0' . substr($cleaned, 4);
        } elseif (str_starts_with($cleaned, '00966')) {
            $cleaned = '0' . substr($cleaned, 5);
        } elseif (str_starts_with($cleaned, '966')) {
            $cleaned = '0' . substr($cleaned, 3);
        } elseif (strlen($cleaned) === 9 && str_starts_with($cleaned, '5')) {
            $cleaned = '0' . $cleaned;
        }

        return $cleaned;
    }
}
