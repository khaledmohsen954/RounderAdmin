<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;

/** التحقق من مدخلات التحقق من رمز OTP. */
class ApiVerifyOtpRequest extends PublicRequest
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

        $otp = trim((string) ($this->input('otp') ?? $this->input('code') ?? ''));

        $this->merge([
            'mobile' => $cleaned,
            'otp' => $otp,
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^05[0-9]{8}$/'],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
        ];
    }
}
