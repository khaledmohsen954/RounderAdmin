<?php

namespace App\Modules\Dashboard\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات DashboardController::__invoke قبل تشغيل الخدمة. */
class AdminDashboard__invokeRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['resident_home_id' => ['nullable', 'integer'], 'status_id' => ['nullable', 'integer'], 'per_page' => ['sometimes', 'integer', 'between:1,50']];
    }
}
