<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;

/** Validate optional service, sub-service, and home filters for the resident provider list. */
class ApiResidentServiceProvidersRequest extends ResidentRequest
{
    public function rules(): array
    {
        return [
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'sub_service' => ['nullable'],
            'sub_service_id' => ['nullable'],
            'home_id' => ['nullable', 'integer'],
        ];
    }
}
