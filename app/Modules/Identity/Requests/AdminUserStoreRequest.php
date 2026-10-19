<?php
namespace App\Modules\Identity\Requests;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
/** إنشاء حساب إدارة فقط؛ لا يقبل الدور أو امتياز المدير العام من العميل. */
class AdminUserStoreRequest extends AdminRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        return ['name'=>['required','string','min:2','max:120'],
            'email'=>['required','email:rfc','max:254','unique:admins,email'],
            'phone'=>['nullable','regex:/^\+[1-9][0-9]{7,14}$/','unique:admins,phone'],
            'locale'=>['required',Rule::in(['ar','en'])],
            'permission_set_id'=>['required','integer','exists:permission_sets,id'],
            'is_active'=>['required','boolean'],
            'password'=>['required','string','min:12','max:72','confirmed','regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']];
    }
}
