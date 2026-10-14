<?php

namespace App\Modules\Community\Requests;
use App\Modules\Community\Enums\ReactionCode;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات CommunityController::react قبل تشغيل الخدمة. */
class ApiCommunityReactRequest extends ResidentRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return ['reaction_code' => ['required', Rule::in(array_column(ReactionCode::cases(), 'value'))]];
    }
}
