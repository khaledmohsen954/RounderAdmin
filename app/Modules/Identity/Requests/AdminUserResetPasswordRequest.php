<?php

namespace App\Modules\Identity\Requests;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات UserController::resetPassword قبل تشغيل الخدمة. */
class AdminUserResetPasswordRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['password'=>['required','string','min:12','max:72',BookingStatusCode::Confirmed->value,'regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']];
    }
}
