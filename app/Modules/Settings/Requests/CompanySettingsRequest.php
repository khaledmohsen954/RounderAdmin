<?php

namespace App\Modules\Settings\Requests;

use App\Core\Http\Requests\AdminRequest;

/**
 * التحقق من مدخلات إعدادات المنشأة الرسمية.
 */
class CompanySettingsRequest extends AdminRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'trade_name' => ['required', 'string', 'min:2', 'max:191'],
            'cr_number' => ['required', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'trademark_registration' => ['nullable', 'string', 'max:191'],
            'legal_representative' => ['required', 'string', 'min:2', 'max:191'],
            'representative_title' => ['required', 'string', 'min:2', 'max:191'],
            'stamp_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:4096'],
            'remove_stamp' => ['nullable', 'boolean'],
            'support_phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:190'],
            'support_working_hours' => ['nullable', 'string', 'max:190'],
            'support_chat_enabled' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'trade_name' => __('Commercial name'),
            'cr_number' => __('Commercial register'),
            'tax_number' => __('Tax number'),
            'trademark_registration' => __('Trademark registration'),
            'legal_representative' => __('Legal representative'),
            'representative_title' => __('Capacity / Title'),
            'stamp_image' => __('Stamp image'),
        ];
    }
}

