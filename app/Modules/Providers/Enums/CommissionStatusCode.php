<?php
namespace App\Modules\Providers\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum CommissionStatusCode: string
{
    case Pending = 'pending';
    case Settled = 'settled';
    case Reversed = 'reversed';
}
