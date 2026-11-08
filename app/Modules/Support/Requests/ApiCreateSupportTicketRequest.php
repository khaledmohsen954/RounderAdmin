<?php

namespace App\Modules\Support\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;

class ApiCreateSupportTicketRequest extends ResidentRequest
{
    public function rules(): array
    {
        return [
            'category' => [
                'required',
                'string',
                Rule::in(['order_issue', 'payment', 'housing', 'other']),
            ],
            'subject' => ['required', 'string', 'max:190'],
            'message' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'category.required' => 'نوع المشكلة أو التصنيف مطلوب.',
            'category.in' => 'التصنيف المختار غير صالح.',
            'subject.required' => 'موضوع الرسالة مطلوب.',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 190 حرفاً.',
            'message.required' => 'شرح المشكلة مطلوب.',
            'message.max' => 'نص الرسالة يجب ألا يتجاوز 5000 حرف.',
            'attachment.image' => 'يجب أن يكون المرفق صورة بصيغة صالحة.',
            'attachment.mimes' => 'صيغ الصور المسموحة هي PNG و JPG فقط.',
            'attachment.max' => 'حجم الصورة يجب ألا يتجاوز 5 ميجابايت.',
        ];
    }
}

