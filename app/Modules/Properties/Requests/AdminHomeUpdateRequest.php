<?php
namespace App\Modules\Properties\Requests;
use Illuminate\Validation\Rule;
/** استثناء رمز العقار الحالي فقط عند التعديل. */
class AdminHomeUpdateRequest extends AdminHomeStoreRequest
{
    /** @return array<string,mixed> */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['property_code'] = ['required','alpha_dash','min:5','max:60',Rule::unique('homes','property_code')->ignore((int)$this->route('homeId'))];
        return $rules;
    }
}
