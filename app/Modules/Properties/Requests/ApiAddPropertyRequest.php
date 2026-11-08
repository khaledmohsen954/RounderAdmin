<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** التحقق من مدخلات إضافة عقار جديد للمستخدم وربطه به. */
class ApiAddPropertyRequest extends ResidentRequest
{
    /**
     * معالجة وتطبيع المدخلات قبل التحقق.
     */
    protected function prepareForValidation(): void
    {
        $unit = $this->input('unit_number') ?? $this->input('unit_label');
        $name = $this->input('name') ?? $this->input('property_name') ?? $this->input('custom_name');

        $type = $this->input('residence_type') ?? $this->input('type') ?? $this->input('property_type');
        if (in_array($type, ['شقة', 'شقه', 'apartment'])) {
            $type = 'apartment';
        } elseif (in_array($type, ['فيلا', 'فله', 'villa', 'بيت', 'منزل'])) {
            $type = 'villa';
        } elseif (in_array($type, ['دور', 'floor'])) {
            $type = 'floor';
        } else {
            $type = $type ?: 'apartment';
        }

        // إذا لم يتم تحديد رقم الوحدة، نضع افتراضياً قيمة مناسبة لنوع العقار (مثلاً "الفيلا" أو "1")
        if (empty($unit)) {
            $unit = ($type === 'villa') ? 'الفيلا' : '1';
        }

        $ownership = $this->input('ownership_type');
        if (in_array($ownership, ['مالك', 'owner'])) {
            $ownership = 'owner';
        } elseif (in_array($ownership, ['مستأجر', 'مستاجر', 'tenant'])) {
            $ownership = 'tenant';
        } else {
            $ownership = $ownership ?: 'owner';
        }

        $address = $this->input('address');
        if (empty($address) && !empty($this->input('district'))) {
            $cityName = $this->input('city_name') ?? '';
            $address = 'حي ' . $this->input('district') . ($cityName ? '، ' . $cityName : '');
        }

        $this->merge([
            'name' => $name ? (string) $name : null,
            'custom_name' => $name ? (string) $name : null,
            'unit_number' => $unit ? (string) $unit : '1',
            'unit_label' => $unit ? (string) $unit : '1',
            'residence_type' => $type,
            'ownership_type' => $ownership,
            'address' => $address ? (string) $address : null,
        ]);
    }

    /**
     * قواعد التحقق من الحقول المسموحة.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isLinkingByCode = !empty($this->input('property_code'));

        return [
            'name' => ['nullable', 'string', 'max:120'],
            'property_name' => ['nullable', 'string', 'max:120'],
            'property_code' => ['nullable', 'string', 'max:60'],
            'address' => [$isLinkingByCode ? 'nullable' : 'required', 'string', 'min:3', 'max:255'],
            'district' => ['nullable', 'string', 'max:120'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'city_name' => ['nullable', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'residence_type' => ['nullable', 'string', 'in:apartment,villa,floor'],
            'unit_number' => ['nullable', 'string', 'max:40'],
            'unit_label' => ['nullable', 'string', 'max:40'],
            'floor_number' => ['nullable', 'string', 'max:40'],
            'custom_name' => ['nullable', 'string', 'max:120'],
            'area_sqm' => ['nullable', 'numeric', 'min:1', 'max:99999'],
            'ownership_type' => ['nullable', 'string', 'in:owner,tenant'],
            'is_primary' => ['nullable', 'boolean'],
        ];
    }

    /**
     * رسائل الخطأ المخصصة بالعربية.
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'address.required' => 'العنوان أو موقع العقار مطلوب.',
            'address.min' => 'يجب ألا يقل العنوان عن 3 أحرف.',
            'city_id.exists' => 'المدينة المختارة غير صالحة.',
            'residence_type.in' => 'نوع العقار يجب أن يكون شقة، فيلا، أو دور.',
        ];
    }
}
