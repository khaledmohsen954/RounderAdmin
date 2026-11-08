<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Core\Support\SaudiPhone;

/** التحقق من مدخلات EmergencyRequestController::store قبل تشغيل الخدمة. */
class ApiEmergencyRequestStoreRequest extends ResidentRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('contact_phone')) {
            $this->merge([
                'contact_phone' => SaudiPhone::normalize((string) $this->input('contact_phone')),
            ]);
        }
    }

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
            'contact_phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX],
            'location_details' => ['required', 'string', 'min:4', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:3000'],
            'confirm_emergency' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_phone.required' => 'رقم هاتف التواصل مطلوب.',
            'contact_phone.regex' => 'رقم هاتف التواصل يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
