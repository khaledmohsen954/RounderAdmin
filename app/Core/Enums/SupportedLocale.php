<?php
namespace App\Core\Enums;
/** اللغات المدعومة في التطبيق؛ لوحة الإدارة تدعم العربية والإنجليزية. */
enum SupportedLocale: string
{
    case Arabic = 'ar';
    case English = 'en';
    case Urdu = 'ur';
}
