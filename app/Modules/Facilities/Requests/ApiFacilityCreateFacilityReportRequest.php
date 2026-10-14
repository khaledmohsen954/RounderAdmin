<?php

namespace App\Modules\Facilities\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات FacilityController::createFacilityReport قبل تشغيل الخدمة. */
class ApiFacilityCreateFacilityReportRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'facility_category_id' => ['required', 'integer', Rule::exists('facility_categories', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'min:4', 'max:160'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'location' => ['nullable', 'string', 'max:160'],
        ];
    }
}
