<?php
namespace App\Modules\Identity\Requests;
use App\Core\Support\SaudiPhone;
use Illuminate\Validation\Rule;
/** إعادة استخدام قواعد الإنشاء مع استثناء السجل الحالي وعدم تغيير كلمة المرور هنا. */
class AdminUserUpdateRequest extends AdminUserStoreRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $rules = parent::rules();
        unset($rules['password']);
        $rules['email'] = ['nullable','email:rfc','max:254',Rule::unique('admins','email')->ignore($this->route('user')->id)];
        $rules['phone'] = ['required','string','regex:' . SaudiPhone::REGEX,Rule::unique('admins','phone')->ignore($this->route('user')->id)];
        return $rules;
    }
}
