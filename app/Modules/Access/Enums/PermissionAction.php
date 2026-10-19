<?php
namespace App\Modules\Access\Enums;
/** الأفعال الثابتة المسموحة لكل قسم. */
enum PermissionAction: string
{
    case View = 'view';
    case Create = 'create';
    case Update = 'update';
    case Delete = 'delete';
}
