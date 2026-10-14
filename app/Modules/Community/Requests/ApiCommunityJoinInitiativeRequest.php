<?php

namespace App\Modules\Community\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات CommunityController::joinInitiative قبل تشغيل الخدمة. */
class ApiCommunityJoinInitiativeRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['pledged_amount' => ['sometimes', 'numeric', 'min:0', 'max:10000000']];
    }
}
