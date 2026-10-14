<?php

namespace App\Modules\Identity\Requests;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات UserController::store قبل تشغيل الخدمة. */
class AdminUserStoreRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['name'=>['required','string','min:2','max:120'],'email'=>['required','email:rfc,dns','max:254','unique:users,email'],'phone'=>['nullable','regex:/^\+[1-9][0-9]{7,14}$/','unique:users,phone'],'role_id'=>['required','integer','exists:roles,id'],'locale'=>['required',Rule::enum(SupportedLocale::class)],'password'=>['required','string','min:12','max:72',BookingStatusCode::Confirmed->value,'regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/']];
    }
}
