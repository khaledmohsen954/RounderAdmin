<?php
namespace App\Modules\Finance\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum PaymentStatusCode: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';
}
