<?php

namespace App\Modules\Emergencies\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** Authorize resident access to the emergency type catalog. */
class ApiEmergencyTypeIndexRequest extends ResidentRequest
{
    public function rules(): array
    {
        return [];
    }
}
