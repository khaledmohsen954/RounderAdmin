<?php

namespace App\Modules\Identity\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;

/** التحقق من مدخلات ProfileController::updateProfile قبل تشغيل الخدمة. */
class ApiProfileUpdateProfileRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['prohibited'],
            'mobile' => ['prohibited'],
            'locale' => ['sometimes', 'required', Rule::in(array_column(SupportedLocale::cases(), 'value'))],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.prohibited' => 'يجب تحديث رقم الجوال عبر التحقق من رمز OTP.',
            'mobile.prohibited' => 'يجب تحديث رقم الجوال عبر التحقق من رمز OTP.',
        ];
    }
}
