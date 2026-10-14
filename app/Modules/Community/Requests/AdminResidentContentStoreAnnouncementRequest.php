<?php

namespace App\Modules\Community\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ResidentContentController::storeAnnouncement قبل تشغيل الخدمة. */
class AdminResidentContentStoreAnnouncementRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'home_id' => ['nullable', 'integer', 'exists:homes,id'],
            'title_ar' => ['required', 'string', 'min:3', 'max:180'],
            'body_ar' => ['required', 'string', 'min:5', 'max:20000'],
            'title_en' => ['required', 'string', 'min:3', 'max:180'],
            'body_en' => ['required', 'string', 'min:5', 'max:20000'],
            'published' => ['required', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
