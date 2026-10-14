<?php
namespace App\Modules\Identity\Enums;
/** أدوار الحسابات الثابتة؛ التسميات المترجمة محفوظة في قاعدة البيانات. */
enum RoleCode: string
{
    case Admin = 'admin';
    case Resident = 'resident';
    case Provider = 'provider';
}
