<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات HomeController::store قبل تشغيل الخدمة. */
class AdminHomeStoreRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['city_id'=>['required','integer',Rule::exists('cities','id')->where('country_code', \App\Modules\Properties\Enums\CountryCode::SaudiArabia->value)->where('is_active',true)],'name'=>['required','string','min:2','max:120'],'address'=>['required','string','max:255'],'property_code'=>['required','alpha_dash','min:5','max:60','unique:homes,property_code'],'health_percent'=>['required','integer','between:0,100'],'association_balance'=>['required','numeric','min:0','max:9999999999.99']];
    }
}
