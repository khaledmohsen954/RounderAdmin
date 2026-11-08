<?php

namespace App\Modules\Catalog\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ServiceCatalogController::__invoke قبل تشغيل الخدمة. */
class ApiServiceCategory__invokeRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'service_category_id' => ['nullable', 'integer'],
            'status_id' => ['nullable', 'integer'],
            'sub_services_limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ];
    }
}
