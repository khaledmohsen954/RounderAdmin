<?php
namespace App\Modules\Identity\Enums;
/** أسماء عمليات المجال المستخدمة في الأحداث. */
enum Operation: string
{
    case ApiAuthRegister = 'ApiAuthService.register';
    case ApiAuthLogin = 'ApiAuthService.login';
    case ApiAuthLogout = 'ApiAuthService.logout';
    case ApiProfileUpdateProfile = 'ApiProfileService.updateProfile';
    case AdminUserDelete = 'AdminUserService.delete';
    case AdminUserStore = 'AdminUserService.store';
    case AdminUserUpdate = 'AdminUserService.update';
    case AdminUserResetPassword = 'AdminUserService.resetPassword';
    case CreateProviderAccount = 'provider-account.create';
}
