<?php

namespace App\Modules\Facilities\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات FacilityController::facilityReports قبل تشغيل الخدمة. */
class ApiFacilityFacilityReportsRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['resident_home_id' => ['nullable', 'integer'], 'status_id' => ['nullable', 'integer'], 'per_page' => ['sometimes', 'integer', 'between:1,50']];
    }
}
