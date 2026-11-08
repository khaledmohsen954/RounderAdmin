<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** التحقق من مدخلات إضافة وحدة سكنية جديدة للساكن. */
class ApiAddUnitRequest extends ResidentRequest
{
    protected function prepareForValidation(): void
    {
        $unit = $this->input('unit_number') ?? $this->input('unit_label');
        $customName = $this->input('custom_name') ?? $this->input('name');

        $type = $this->input('residence_type');
        if ($type === 'شقة') $type = 'apartment';
        elseif ($type === 'فيلا') $type = 'villa';
        elseif ($type === 'دور') $type = 'floor';

        $ownership = $this->input('ownership_type');
        if ($ownership === 'مالك') $ownership = 'owner';
        elseif ($ownership === 'مستأجر') $ownership = 'tenant';

        $this->merge([
            'unit_number' => $unit ? (string) $unit : null,
            'custom_name' => $customName ? (string) $customName : null,
            'residence_type' => $type ? (string) $type : 'apartment',
            'ownership_type' => $ownership ? (string) $ownership : 'owner',
        ]);
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'min:3', 'max:255'],
            'district' => ['nullable', 'string', 'max:120'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'city_name' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'residence_type' => ['required', 'string', 'in:apartment,villa,floor'],
            'unit_number' => ['required', 'string', 'max:40'],
            'floor_number' => ['nullable', 'string', 'max:40'],
            'custom_name' => ['nullable', 'string', 'max:120'],
            'area_sqm' => ['nullable', 'numeric', 'min:1', 'max:99999'],
            'ownership_type' => ['nullable', 'string', 'in:owner,tenant'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'address.required' => 'العنوان أو الموقع مطلوب.',
            'unit_number.required' => 'رقم الوحدة مطلوب.',
            'residence_type.required' => 'نوع السكن مطلوب (شقة، فيلا، دور).',
        ];
    }
}
