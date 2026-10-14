<?php
namespace App\Modules\Catalog\Requests;
use Illuminate\Validation\Rule;
/** نفس قواعد إنشاء الباقة مع استثناء السجل الحالي من تفرد الكود. */
class AdminPackageAdminUpdatePackageRequest extends AdminPackageAdminStorePackageRequest
{
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return array_replace(parent::rules(), [
            'code' => ['required', 'alpha_dash', 'min:2', 'max:60', Rule::unique('service_packages', 'code')->ignore($this->route('packageId'))],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
