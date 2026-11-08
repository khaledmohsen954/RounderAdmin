<?php

namespace App\Modules\Properties\Requests;

use App\Modules\Properties\Enums\CountryCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** يتحقق من جميع عمليات المدن في لوحة الإدارة (عرض، إنشاء، تعديل). */
class AdminCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return [
                'search' => ['nullable', 'string', 'max:100'],
                'region_id' => ['nullable', 'integer', 'exists:regions,id'],
                'status' => ['nullable', 'string', 'in:all,active,inactive'],
            ];
        }

        $cityId = $this->route('cityId');

        return [
            'code' => ['required', 'string', 'max:60', 'alpha_dash', Rule::unique('cities', 'code')->ignore($cityId)],
            'region_id' => ['nullable', 'integer', 'exists:regions,id'],
            'country_code' => ['nullable', 'string', Rule::enum(CountryCode::class)],
            'name_ar' => ['required', 'string', 'max:120'],
            'name_en' => ['nullable', 'string', 'max:120'],
            'name_ur' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}

