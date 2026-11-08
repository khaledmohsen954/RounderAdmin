<?php
namespace App\Modules\Identity\Requests;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use App\Core\Support\SaudiPhone;

/** إنشاء حساب إدارة فقط؛ لا يقبل الدور أو امتياز المدير العام من العميل. */
class AdminUserStoreRequest extends AdminRequest
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

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['nullable', 'email:rfc', 'max:254', 'unique:admins,email'],
            'phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX, 'unique:admins,phone'],
            'locale' => ['required', Rule::in(['ar', 'en'])],
            'permission_set_id' => ['required', 'integer', 'exists:permission_sets,id'],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'رقم الهاتف مطلوب.',
            'phone.regex' => 'رقم الهاتف يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
