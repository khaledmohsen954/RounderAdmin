<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات AuthController::login قبل تشغيل الخدمة. */
class ApiAuthLoginRequest extends PublicRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['email'=>['required','email:rfc'],'password'=>['required','string','max:72']];
    }
}
