<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;

/** التحقق من مدخلات إرسال رمز التحقق OTP. */
class ApiSendOtpRequest extends PublicRequest
{
    protected function prepareForValidation(): void
    {
        $raw = (string) ($this->input('mobile') ?? $this->input('phone') ?? '');
        $country = (string) ($this->input('country_code') ?? '+966');

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
            'country_code' => $country,
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^05[0-9]{8}$/'],
            'country_code' => ['nullable', 'string', 'max:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
