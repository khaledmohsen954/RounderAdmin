<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** يتحقق من بيانات تعديل قسم خدمة. */
class AdminServiceCategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $id = (int) $this->route('categoryId');

        return [
            'code' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('service_categories', 'code')->ignore($id)],
            'name_ar' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'name_ur' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
