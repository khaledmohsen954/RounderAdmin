<?php

namespace App\Modules\Catalog\Requests;
use App\Core\Enums\SupportedLocale;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات PackageAdminController::updateService قبل تشغيل الخدمة. */
class AdminPackageAdminUpdateServiceRequest extends AdminRequest
{
    /**
     * توحيد كود الكتالوج قبل التحقق من التكرار.
     * @return void
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['code' => Str::lower((string) $this->input('code'))]);
    }
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        $rules = [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60',
                \Illuminate\Validation\Rule::unique('services', 'code')->ignore($this->route('serviceId'))],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'is_active' => ['required', 'boolean'],
        ];
        foreach (array_column(SupportedLocale::cases(), 'value') as $locale) {
            $rules['name_'.$locale] = ['required', 'string', 'min:2', 'max:160'];
            $rules['description_'.$locale] = ['nullable', 'string', 'max:5000'];
        }
        
return $rules;
    }
}
