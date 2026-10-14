<?php
namespace App\Modules\Providers\Enums;
/** مصدر قيد العمولة ومفتاح علاقته في قاعدة البيانات. */
enum CommissionSource: string
{
    case Booking = 'booking_id';
    case Emergency = 'emergency_request_id';
}
