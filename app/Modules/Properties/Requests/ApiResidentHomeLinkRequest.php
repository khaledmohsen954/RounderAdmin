<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ResidentHomeController::link قبل تشغيل الخدمة. */
class ApiResidentHomeLinkRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['property_code'=>['required','string','alpha_dash','exists:homes,property_code'],'unit_label'=>['required','string','min:1','max:40']];
    }
}
