<?php
namespace App\Modules\Access\Requests;
use App\Core\Http\Requests\AdminRequest;
/** تفويض عرض أو حذف مجموعة الصلاحيات يكتمل في وسيط القسم. */
class PermissionSetReadRequest extends AdminRequest
{
    /** @return array<string, mixed> */
    public function rules(): array { return []; }
}
