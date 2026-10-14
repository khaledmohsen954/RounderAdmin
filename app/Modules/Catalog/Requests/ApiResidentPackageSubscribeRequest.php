<?php

namespace App\Modules\Catalog\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ResidentPackageController::subscribe قبل تشغيل الخدمة. */
class ApiResidentPackageSubscribeRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'resident_home_id' => [
                'required', 'integer',
                Rule::exists('resident_homes', 'id')->where('user_id', $this->user()->id),
            ],
            'service_package_id' => [
                'required', 'integer',
                Rule::exists('service_packages', 'id')->where('is_active', true),
            ],
        ];
    }
}
