<?php
namespace App\Core\Enums;
/** أسباب فشل قواعد المجال؛ تحويلها إلى HTTP مسؤولية الطبقة المشتركة. */
enum FailureReason: string
{
    case NotFound = 'not_found';
    case Forbidden = 'forbidden';
    case Unauthorized = 'unauthorized';
    case Conflict = 'conflict';
    case InvalidInput = 'invalid_input';
    case Configuration = 'configuration';
}
