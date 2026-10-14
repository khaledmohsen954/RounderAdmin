<?php

namespace App\Modules\Catalog\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات PackageAdminController::storeService قبل تشغيل الخدمة. */
class AdminPackageAdminStoreServiceRequest extends AdminRequest
{
    use CatalogPricingRules;
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
        return array_merge($this->pricingRules(), [
            'service_category_id' => ['required', 'integer', 'exists:service_categories,id'],
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60', 'unique:services,code'],
            'base_price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'name_ar' => ['required', 'string', 'min:2', 'max:160'],
            'name_en' => ['required', 'string', 'min:2', 'max:160'],
            'name_ur' => ['required', 'string', 'min:2', 'max:160'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ur' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
