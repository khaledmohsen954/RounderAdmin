<?php
namespace App\Modules\Identity\Requests;
use App\Core\Http\Requests\PublicRequest;
/** التحقق من بيانات دخول لوحة الإدارة؛ صلاحية الدور تتحقق منها خدمة الدخول. */
class AdminLoginRequest extends PublicRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string', 'max:200'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'البريد الإلكتروني أو رقم الهاتف مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ];
    }
}
