<?php

namespace App\Modules\Identity\Requests;

use App\Core\Http\Requests\PublicRequest;
use Illuminate\Validation\Rule;

class ApiSocialLoginRequest extends PublicRequest
{
    public function rules(): array
    {
        return [
            'provider' => ['required', 'string', Rule::in(['google', 'apple'])],
            'id_token' => ['required', 'string', 'max:8192'],
        ];
    }
}
