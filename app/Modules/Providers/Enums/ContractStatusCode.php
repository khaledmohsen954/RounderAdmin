<?php
namespace App\Modules\Providers\Enums;
/** الخيارات الثابتة للمجال؛ لا تتغير بتعديل التسميات المترجمة. */
enum ContractStatusCode: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
}
