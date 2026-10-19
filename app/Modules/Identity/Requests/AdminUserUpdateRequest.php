<?php
namespace App\Modules\Identity\Requests;
use Illuminate\Validation\Rule;
/** إعادة استخدام قواعد الإنشاء مع استثناء السجل الحالي وعدم تغيير كلمة المرور هنا. */
class AdminUserUpdateRequest extends AdminUserStoreRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['password']);
        $rules['email'] = ['required','email:rfc','max:254',Rule::unique('admins','email')->ignore($this->route('user')->id)];
        $rules['phone'] = ['nullable','regex:/^\+[1-9][0-9]{7,14}$/',Rule::unique('admins','phone')->ignore($this->route('user')->id)];
        return $rules;
    }
}
