<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات EmergencyAdminController::update قبل تشغيل الخدمة. */
class AdminEmergencyAdminUpdateRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'provider_profile_id' => ['nullable', 'integer', 'exists:provider_profiles,id'],
            'status_id' => ['required', 'integer', 'exists:emergency_request_statuses,id'],
            'gross_amount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
