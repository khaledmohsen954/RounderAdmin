<?php
namespace App\Modules\Catalog\Requests;
use App\Core\Http\Requests\AdminRequest;
/** صلاحية الإدارة مطلوبة لقراءة نموذج التعديل. */
class AdminPackageAdminEditPackageRequest extends AdminRequest
{
    /** @return array<string, mixed> */
    public function rules(): array { return []; }
}
