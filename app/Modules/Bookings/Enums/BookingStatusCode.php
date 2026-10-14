<?php
namespace App\Modules\Bookings\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum BookingStatusCode: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
