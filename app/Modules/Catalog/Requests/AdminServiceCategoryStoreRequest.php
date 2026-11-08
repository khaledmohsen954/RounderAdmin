<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** يتحقق من بيانات إنشاء قسم خدمة. */
class AdminServiceCategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:60', 'alpha_dash', 'unique:service_categories,code'],
            'name_ar' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'name_ur' => ['nullable', 'string', 'max:120'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
