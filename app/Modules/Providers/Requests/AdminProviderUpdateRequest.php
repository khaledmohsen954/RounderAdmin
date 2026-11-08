<?php

namespace App\Modules\Providers\Requests;

use App\Core\Enums\SupportedLocale;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;

use App\Core\Support\SaudiPhone;

/** تعديل بيانات الشركة والحساب؛ تغيير كلمة المرور له مسار إدارة الحسابات المخصص. */
class AdminProviderUpdateRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('phone') || $this->has('mobile')) {
            $raw = (string) ($this->input('phone') ?? $this->input('mobile') ?? '');
            $this->merge([
                'phone' => SaudiPhone::normalize($raw),
            ]);
        }

        if ($this->boolean('_service_ids_submitted') && !$this->exists('service_ids')) {
            $this->merge(['service_ids' => []]);
        }
    }

    /**
     * يتحدد استثناء التفرد من السجل المرتبط بالمسار، وليس من user_id مرسل من العميل.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $provider = $this->route('provider');
        $userId = $provider->user_id;
        $rules = [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, Rule::unique('users', 'phone')->ignore($userId)],
            'locale' => ['required', Rule::enum(SupportedLocale::class)],
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
            '_service_ids_submitted' => ['sometimes', 'accepted'],
            'contract_id'=>['nullable','integer'],
            'service_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'emergency_commission_percent'=>['required_with:contract_id','numeric','between:0,100','decimal:0,2'],
            'starts_on'=>['required_with:contract_id','date'],
            'ends_on'=>['nullable','date','after_or_equal:starts_on'],
            'terms'=>['nullable','string','max:250000'],
        ];

        $registrationProfile = filled($provider->provider_type) || filled($this->input('provider_type'));
        if ($this->boolean('is_approved') && $registrationProfile) {
            $rules['provider_type'] = ['required', Rule::in(['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'])];
            $rules['years_of_experience'] = ['required', 'integer', 'between:0,100'];
            $rules['terms'] = ['required', 'string', 'max:250000'];
            foreach (['trade_name' => 160, 'commercial_registration' => 80, 'legal_representative' => 160, 'legal_representative_id' => 30] as $field => $max) {
                $rules[$field] = ['required', 'string', 'max:' . $max];
            }
            foreach (array_keys(\App\Modules\Providers\Services\ProviderDocumentService::DOCUMENTS) as $field) {
                $required = !$provider->getAttribute($field);
                $rules[$field] = [$required ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'];
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
