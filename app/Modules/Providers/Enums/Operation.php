<?php
namespace App\Modules\Providers\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case AdminProviderStore = 'AdminProviderService.store';
    case RecordCommission = 'commission.record';
}
