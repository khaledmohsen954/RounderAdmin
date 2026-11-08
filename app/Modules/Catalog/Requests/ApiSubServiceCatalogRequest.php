<?php

namespace App\Modules\Catalog\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * يتحقق من طلب جلب الخدمات الفرعية حسب الخدمة؛ يقبل service_id من الرابط أو الاستعلام ويدعم ترقيم الصفحات والبحث.
 */
class ApiSubServiceCatalogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('serviceId') !== null) {
            $this->merge(['service_id' => $this->route('serviceId')]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
