<?php
namespace App\Modules\Identity\Services;
use App\Modules\Identity\Enums\RoleCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
/** سياسة مصادقة المدير؛ دورة جلسة HTTP تبقى في المتحكم. */
class AdminAuthService
{
    /**
     * يمنع إنشاء جلسة إدارة لحساب ساكن أو مزود.
     * @param array<string, mixed> $input
     * @return void
     * @throws ValidationException إذا لم تكن بيانات المدير صحيحة.
     */
    public function login(array $input): void
    {
        if (!Auth::attempt(['email' => $input['email'], 'password' => $input['password']],
            (bool) ($input['remember'] ?? false)) || Auth::user()->role?->code !== RoleCode::Admin->value) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
    }
    /**
     * إغلاق المصادقة قبل إبطال جلسة المتصفح.
     * @return void
     */
    public function logout(): void { Auth::logout(); }
}
