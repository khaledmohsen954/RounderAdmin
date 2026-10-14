<?php

namespace App\Modules\Identity\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات UserController::update قبل تشغيل الخدمة. */
class AdminUserUpdateRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['name'=>['sometimes','required','string','min:2','max:120'],'email'=>['sometimes','required','email:rfc,dns','max:254',Rule::unique('users','email')->ignore($this->route('user')->id)],'phone'=>['sometimes','nullable','regex:/^\+[1-9][0-9]{7,14}$/',Rule::unique('users','phone')->ignore($this->route('user')->id)],'role_id'=>['sometimes','required','integer','exists:roles,id'],'locale'=>['sometimes','required',Rule::enum(SupportedLocale::class)]];
    }
}
