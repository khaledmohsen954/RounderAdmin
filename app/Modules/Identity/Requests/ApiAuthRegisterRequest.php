<?php

namespace App\Modules\Identity\Requests;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\PublicRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات AuthController::register قبل تشغيل الخدمة. */
class ApiAuthRegisterRequest extends PublicRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
   'name'=>['required','string','min:2','max:120'],
   'email'=>['required','string','email:rfc','max:254','unique:users,email'],
   'phone'=>['nullable','string','regex:/^\+[1-9][0-9]{7,14}$/','unique:users,phone'],
   'password'=>['required','string','min:10','max:72',BookingStatusCode::Confirmed->value,'regex:/[a-z]/','regex:/[A-Z]/','regex:/[0-9]/'],
   'locale'=>['sometimes',Rule::enum(SupportedLocale::class)],
  ];
    }
}
