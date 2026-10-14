<?php
namespace App\Modules\Catalog\Requests;
use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
/** التحقق والتفويض لتعريف بند مقارنة. */
class SavePackageFeatureRequest extends AdminRequest
{
    /** @return void */
    protected function prepareForValidation(): void
    {
        $this->merge(['code'=>Str::lower((string)$this->input('code'))]);
    }
    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code'=>['required','alpha_dash','min:2','max:60',Rule::unique('package_features','code')->ignore($this->route('featureId'))],
            'name_ar'=>['required','string','min:2','max:160'],
            'name_en'=>['required','string','min:2','max:160'],
            'name_ur'=>['required','string','min:2','max:160'],
            'sort_order'=>['required','integer','between:0,65535'],
            'is_active'=>['required','boolean'],
        ];
    }
}
