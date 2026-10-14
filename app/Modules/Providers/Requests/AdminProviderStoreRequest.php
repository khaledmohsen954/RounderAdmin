<?php

namespace App\Modules\Providers\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ProviderController::store قبل تشغيل الخدمة. */
class AdminProviderStoreRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:254', 'unique:users,email'],
            'phone' => ['required', 'regex:/^\+[1-9][0-9]{7,14}$/', 'unique:users,phone'],
            'locale' => ['required', Rule::in(array_column(SupportedLocale::cases(), 'value'))],
            'password' => ['required', 'string', 'min:12', 'max:72', 'confirmed', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'company_name' => ['required', 'string', 'min:2', 'max:160'],
            'is_approved' => ['required', 'boolean'],
            'service_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'emergency_commission_percent' => ['required', 'numeric', 'between:0,100'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'terms' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
