<?php
namespace App\Modules\Access\Requests;
use App\Core\Http\Requests\AdminRequest;
use App\Modules\Access\Services\AdminPermissionService;
use Illuminate\Validation\Rule;
/** لا يسمح إدخال صلاحيات خارج الأقسام والأفعال المعروفة. */
class PermissionSetRequest extends AdminRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return ['name_ar'=>['required','string','min:2','max:120'],
            'name_en'=>['required','string','min:2','max:120'],
            'permissions'=>['sometimes','array'],
            'permissions.*'=>['string','distinct',Rule::in(app(AdminPermissionService::class)->keys())]];
    }
}
