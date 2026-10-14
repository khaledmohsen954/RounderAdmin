<?php

namespace App\Modules\Community\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات CommunityController::createCommunityPost قبل تشغيل الخدمة. */
class ApiCommunityCreateCommunityPostRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:2', 'max:5000']];
    }
}
