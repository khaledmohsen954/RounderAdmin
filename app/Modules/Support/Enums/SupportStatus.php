<?php

namespace App\Modules\Support\Enums;

enum SupportStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'جديدة',
            self::InProgress => 'قيد المتابعة',
            self::Resolved => 'تم الرد والحل',
            self::Closed => 'مغلقة',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
            self::InProgress => 'bg-info-subtle text-info-emphasis border border-info-subtle',
            self::Resolved => 'bg-success-subtle text-success border border-success-subtle',
            self::Closed => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        };
    }
}

