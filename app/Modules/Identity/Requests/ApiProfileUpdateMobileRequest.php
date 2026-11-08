<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\ResidentRequest;
use App\Core\Support\SaudiPhone;
use Illuminate\Validation\Rule;

class ApiProfileUpdateMobileRequest extends ResidentRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'mobile' => SaudiPhone::normalize((string) ($this->input('mobile') ?? $this->input('phone') ?? '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'mobile' => [
                'required',
                'string',
                'regex:' . SaudiPhone::REGEX,
                Rule::unique('users', 'phone')->ignore($this->user()->id),
            ],
            'otp' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'mobile.unique' => 'رقم الجوال مستخدم بالفعل.',
            'otp.required' => 'رمز التحقق مطلوب.',
            'otp.digits' => 'رمز التحقق يجب أن يتكون من 6 أرقام.',
        ];
    }
}
