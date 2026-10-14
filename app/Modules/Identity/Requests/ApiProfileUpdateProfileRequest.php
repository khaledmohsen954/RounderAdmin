<?php

namespace App\Modules\Identity\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'email' => ['sometimes', 'required', 'email:rfc', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'phone' => ['sometimes', 'nullable', 'regex:/^\+[1-9]\d{7,14}$/', Rule::unique('users', 'phone')->ignore($this->user()->id)],
            'locale' => ['sometimes', 'required', Rule::in(array_column(SupportedLocale::cases(), 'value'))],
        ];
    }
}
