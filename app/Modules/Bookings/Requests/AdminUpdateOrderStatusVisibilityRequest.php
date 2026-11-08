<?php

namespace App\Modules\Bookings\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateOrderStatusVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof \App\Modules\Identity\Models\Admin;
    }

    protected function prepareForValidation(): void
    {
        $singleId = $this->input('status_id', $this->input('id'));
        if ($singleId !== null) {
            $this->merge(['status_id' => $singleId]);
        }
    }

    public function rules(): array
    {
        if ($this->filled('type') && $this->filled('status_id')) {
            return [
                'type' => ['required', 'in:booking,emergency'],
                'status_id' => ['required', 'integer'],
                'show_in_provider_portal' => ['required', 'boolean'],
            ];
        }

        return [
            'booking_statuses' => ['nullable', 'array'],
            'booking_statuses.*' => ['integer', 'exists:booking_statuses,id'],
            'booking_status_ids' => ['nullable', 'array'],
            'booking_status_ids.*' => ['integer', 'exists:booking_statuses,id'],
            'emergency_statuses' => ['nullable', 'array'],
            'emergency_statuses.*' => ['integer', 'exists:emergency_request_statuses,id'],
            'emergency_status_ids' => ['nullable', 'array'],
            'emergency_status_ids.*' => ['integer', 'exists:emergency_request_statuses,id'],
        ];
    }
}

