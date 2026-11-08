<?php

namespace App\Modules\Identity\Requests;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\PublicRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات AuthController::register قبل تشغيل الخدمة. */
class ApiAuthRegisterRequest extends PublicRequest
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
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'mobile' => ['required', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')],
            'phone' => ['sometimes', 'nullable', 'string', 'regex:/^05[0-9]{8}$/', Rule::unique('users', 'phone')],
            'email' => ['sometimes', 'nullable', 'string', 'email:rfc', 'max:254', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6', 'max:72', BookingStatusCode::Confirmed->value],
            'locale' => ['sometimes', Rule::enum(SupportedLocale::class)],
        ];
    }

    /**
     * رسائل التحقق المخصصة.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'mobile.required' => 'رقم الجوال مطلوب.',
            'mobile.regex' => 'رقم الجوال يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
