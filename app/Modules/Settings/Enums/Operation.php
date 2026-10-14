<?php
namespace App\Modules\Settings\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case UpdateLocale = 'locale.update';
}
