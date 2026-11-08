<?php

namespace App\Modules\Support\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateSupportTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof \App\Modules\Identity\Models\Admin;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['open', 'in_progress', 'resolved', 'closed'])],
            'admin_reply' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'حالة التذكرة مطلوبة.',
            'status.in' => 'حالة التذكرة المحددة غير صالحة.',
            'admin_reply.max' => 'الرد يجب ألا يتجاوز 5000 حرف.',
        ];
    }
}

