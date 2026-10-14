<?php

namespace App\Modules\Community\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات ResidentContentController::storeInitiative قبل تشغيل الخدمة. */
class AdminResidentContentStoreInitiativeRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'home_id' => ['required', 'integer', 'exists:homes,id'],
            'title_ar' => ['required', 'string', 'min:3', 'max:180'],
            'description_ar' => ['required', 'string', 'min:5', 'max:5000'],
            'title_en' => ['required', 'string', 'min:3', 'max:180'],
            'description_en' => ['required', 'string', 'min:5', 'max:5000'],
            'target_amount' => ['nullable', 'numeric', 'min:0', 'max:100000000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'is_published' => ['required', 'boolean'],
        ];
    }
}
