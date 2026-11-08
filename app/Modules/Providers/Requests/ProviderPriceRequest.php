<?php

namespace App\Modules\Providers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderPriceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('city_ids_json'))) {
            $cityIds = json_decode($this->input('city_ids_json'), true);
            if (is_array($cityIds)) {
                $this->merge(['city_ids' => $cityIds]);
            }
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'sub_service_id' => ['required', 'integer', 'exists:sub_services,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
            'city_ids' => ['required', 'array', 'min:1'],
            'city_ids.*' => ['required', 'integer', 'distinct', 'exists:cities,id'],
            'price' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
        ];
    }
}
