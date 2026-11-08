<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** التحقق من مدخلات حذف السكن أو الوحدة للساكن. */
class ApiDeleteUnitRequest extends ResidentRequest
{
    /**
     * دمج معرّفات المسار من الـ Route مع المدخلات.
     */
    public function all($keys = null)
    {
        $data = parent::all($keys);
        if ($this->route('id') && empty($data['id'])) {
            $data['id'] = $this->route('id');
        }
        if ($this->route('homeId') && empty($data['home_id'])) {
            $data['home_id'] = $this->route('homeId');
        }
        return $data;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer'],
            'home_id' => ['nullable', 'integer'],
            'resident_home_id' => ['nullable', 'integer'],
            'property_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $hasId = !empty($this->input('id'))
                || !empty($this->input('home_id'))
                || !empty($this->input('resident_home_id'))
                || !empty($this->input('property_id'))
                || !empty($this->route('id'));

            if (!$hasId) {
                $v->errors()->add('home_id', 'معرف السكن أو الوحدة مطلوب للحذف (home_id أو resident_home_id).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'home_id.integer' => 'معرف السكن يجب أن يكون رقماً صحيحاً.',
            'resident_home_id.integer' => 'معرف الوحدة يجب أن يكون رقماً صحيحاً.',
        ];
    }
}

