<?php

namespace App\Modules\Catalog\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات PackageAdminController::storePackage قبل تشغيل الخدمة. */
class AdminPackageAdminStorePackageRequest extends AdminRequest
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
            'billing_cycle_id' => ['required', 'integer', 'exists:package_billing_cycles,id'],
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60', 'unique:service_packages,code'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'currency_code' => ['required', 'string', 'size:3', 'regex:/^[A-Z]{3}$/'],
            'duration_months' => ['required', 'integer', 'between:1,120'],
            'name_ar' => ['required', 'string', 'min:2', 'max:140'],
            'name_en' => ['required', 'string', 'min:2', 'max:140'],
            'name_ur' => ['required', 'string', 'min:2', 'max:140'],
            'description_ar' => ['nullable', 'string', 'max:5000'],
            'description_en' => ['nullable', 'string', 'max:5000'],
            'description_ur' => ['nullable', 'string', 'max:5000'],
            'features' => ['sometimes', 'array', 'max:200'],
            'features.*.package_feature_id' => ['required','integer','distinct','exists:package_features,id'],
            'features.*.availability' => ['required', Rule::enum(\App\Modules\Catalog\Enums\FeatureAvailability::class)],
            'features.*.value_ar' => ['nullable','string','max:500'],
            'features.*.value_en' => ['nullable','string','max:500'],
            'features.*.value_ur' => ['nullable','string','max:500'],
            'services' => ['required', 'array', 'min:1', 'max:100'],
            'services.*.service_id' => ['required', 'integer', 'distinct', 'exists:services,id'],
            'services.*.included_quantity' => ['nullable', 'integer', 'between:1,1000'],
            'services.*.is_unlimited' => ['required', 'boolean'],
        ]);
    }
}
