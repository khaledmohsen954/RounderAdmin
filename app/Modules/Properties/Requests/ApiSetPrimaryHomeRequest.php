<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** التحقق من مدخلات تعيين الوحدة الأساسية للساكن. */
class ApiSetPrimaryHomeRequest extends ResidentRequest
{
    public function rules(): array
    {
        return [
            'home_id' => ['nullable', 'integer', 'exists:homes,id', 'required_without:resident_home_id'],
            'resident_home_id' => ['nullable', 'integer', 'exists:resident_homes,id', 'required_without:home_id'],
        ];
    }

    public function messages(): array
    {
        return [
            'home_id.required_without' => 'معرف السكن (home_id أو resident_home_id) مطلوب.',
            'resident_home_id.required_without' => 'معرف السكن (home_id أو resident_home_id) مطلوب.',
        ];
    }
}
