<?php

namespace App\Modules\Providers\Requests;

use App\Core\Support\SaudiPhone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderRegistrationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['phone' => SaudiPhone::normalize((string) $this->input('phone'))]);
    }

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'trade_name' => ['required', 'string', 'min:2', 'max:160'],
            'provider_type' => ['required', Rule::in(['فني مستقل', 'مؤسسة خدمات', 'شركة صيانة'])],
            'years_of_experience' => ['required', 'integer', 'between:0,100'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['required', 'integer', 'distinct', Rule::exists('services', 'id')->where('is_active', true)],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6','confirmed'],
            'company_seal' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'commercial_registration_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'legal_representative_id_document' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'signed_annex_a' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'municipal_license' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ];
    }
}