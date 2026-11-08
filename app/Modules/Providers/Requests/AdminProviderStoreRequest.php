<?php

namespace App\Modules\Providers\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use App\Core\Support\SaudiPhone;

/** التحقق من مدخلات ProviderController::store قبل تشغيل الخدمة. */
class AdminProviderStoreRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone') || $this->has('mobile')) {
            $raw = (string) ($this->input('phone') ?? $this->input('mobile') ?? '');
            $this->merge([
                'phone' => SaudiPhone::normalize($raw),
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
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, 'unique:users,phone'],
            'locale' => ['required', Rule::in(array_column(SupportedLocale::cases(), 'value'))],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'trade_name' => ['nullable', 'string', 'min:2', 'max:160'],
            'provider_type' => ['nullable', 'string', 'max:80'],
            'years_of_experience' => ['nullable', 'integer', 'between:0,100'],
            'commercial_registration' => ['nullable', 'string', 'max:80'],
            'legal_representative' => ['nullable', 'string', 'max:160'],
            'legal_representative_id' => ['nullable', 'string', 'max:30'],
            'company_seal' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'commercial_registration_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'legal_representative_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'signed_annex_a' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'municipal_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'is_approved' => ['required', 'boolean'],
            'service_ids' => ['sometimes', 'array'],
            'service_ids.*' => ['integer', 'distinct', 'exists:services,id'],
            'service_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'emergency_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'terms' => ['nullable', 'string', 'max:250000'],
        ];

        if ($this->boolean('is_approved') && $this->filled('provider_type')) {
            $rules['provider_type'] = ['required', Rule::in(['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'])];
            $rules['years_of_experience'] = ['required', 'integer', 'between:0,100'];
            foreach (['trade_name' => 160, 'commercial_registration' => 80, 'legal_representative' => 160, 'legal_representative_id' => 30] as $field => $max) {
                $rules[$field] = ['required', 'string', 'max:' . $max];
            }
            foreach (array_keys(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS) as $field) {
                $rules[$field] = ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.required' => __('Phone number is required.'),
            'phone.regex' => __('Phone number must start with 05 and contain 10 digits.'),
            'password.required' => __('Password is required.'),
            'password.min' => __('Password must be at least 8 characters.'),
            'password.regex' => __('Password must contain at least one uppercase letter (A-Z), one lowercase letter (a-z), and one number (0-9).'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];
    }
}
