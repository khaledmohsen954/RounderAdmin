<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** يتحقق من بيانات تعديل خدمة فرعية. */
class AdminSubServiceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = (int) $this->route('subServiceId');

        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'code' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('sub_services', 'code')->ignore($id)],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:2000'],
            'description_en' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
