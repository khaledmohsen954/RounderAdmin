<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** يتحقق من طلب عرض قائمة الخدمات الفرعية. */
class AdminSubServiceIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
