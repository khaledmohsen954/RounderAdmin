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
        return ['email' => ['required','email:rfc','max:254'],
            'password' => ['required','string','max:200'], 'remember' => ['sometimes','boolean']];
    }
}
