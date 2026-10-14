<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات EmergencyRequestController::store قبل تشغيل الخدمة. */
class ApiEmergencyRequestStoreRequest extends ResidentRequest
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
            'emergency_type_id' => [
                'required', 'integer',
                Rule::exists('emergency_types', 'id')->where('is_active', true),
            ],
            'contact_phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/'],
            'location_details' => ['required', 'string', 'min:4', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'confirm_emergency' => ['required', 'accepted'],
        ];
    }
}
