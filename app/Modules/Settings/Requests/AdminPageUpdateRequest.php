<?php

namespace App\Modules\Settings\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * يتحقق من بيانات تعديل الصفحة الثابتة في لوحة الإدارة.
 */
class AdminPageUpdateRequest extends FormRequest
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
            'title_ar' => ['required', 'string', 'max:255'],
            'content_ar' => ['required', 'string'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'content_en' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * تسميات الحقول للرسائل باللغة العربية.
     */
    public function attributes(): array
    {
        return [
            'title_ar' => __('Page title in Arabic'),
            'content_ar' => __('Page content in Arabic'),
            'title_en' => __('Page title in English'),
            'content_en' => __('Page content in English'),
            'is_active' => __('Status'),
        ];
    }
}

