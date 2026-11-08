<?php

namespace App\Modules\Support\Enums;

enum SupportCategory: string
{
    case OrderIssue = 'order_issue';
    case Payment = 'payment';
    case Housing = 'housing';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OrderIssue => 'مشكلة في طلب',
            self::Payment => 'الدفع',
            self::Housing => 'السكن',
            self::Other => 'أخرى',
        };
    }
}

