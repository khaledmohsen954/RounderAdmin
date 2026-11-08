<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات AuthController::login قبل تشغيل الخدمة. */
class ApiAuthLoginRequest extends PublicRequest
{
    protected function prepareForValidation(): void
    {
        $raw = (string) ($this->input('mobile') ?? $this->input('phone') ?? '');
        $cleaned = preg_replace('/[^\d+]/', '', $raw);

        if (str_starts_with($cleaned, '+966')) {
            $cleaned = '0' . substr($cleaned, 4);
        } elseif (str_starts_with($cleaned, '00966')) {
            $cleaned = '0' . substr($cleaned, 5);
        } elseif (str_starts_with($cleaned, '966')) {
            $cleaned = '0' . substr($cleaned, 3);
        } elseif (strlen($cleaned) === 9 && str_starts_with($cleaned, '5')) {
            $cleaned = '0' . $cleaned;
        }

        $this->merge([
            'mobile' => $cleaned,
            'phone' => $cleaned,
        ]);
    }

    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^05[0-9]{8}$/'],
            'password' => ['required', 'string', 'max:72'],
        ];
    }

    /**
     * رسائل التحقق المخصصة.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
