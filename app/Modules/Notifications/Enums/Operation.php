<?php
namespace App\Modules\Notifications\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case ApiNotificationMarkNotificationRead = 'ApiNotificationService.markNotificationRead';
}
