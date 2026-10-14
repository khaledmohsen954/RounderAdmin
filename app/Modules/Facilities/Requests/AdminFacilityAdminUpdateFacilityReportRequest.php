<?php

namespace App\Modules\Facilities\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات FacilityAdminController::updateFacilityReport قبل تشغيل الخدمة. */
class AdminFacilityAdminUpdateFacilityReportRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'status_id' => ['required', 'integer', 'exists:booking_statuses,id'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
