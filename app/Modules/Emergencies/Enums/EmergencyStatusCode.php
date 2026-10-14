<?php
namespace App\Modules\Emergencies\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum EmergencyStatusCode: string
{
    case Received = 'received';
    case Assigned = 'assigned';
    case OnTheWay = 'on_the_way';
    case Resolved = 'resolved';
    case Cancelled = 'cancelled';
}
