<?php

namespace App\Modules\Properties\Requests;

use App\Core\Http\Requests\ResidentRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

use Carbon\Carbon;

use App\Core\Support\SaudiPhone;

/** التحقق من مدخلات VisitController::createVisit قبل تشغيل الخدمة. */
class ApiVisitCreateVisitRequest extends ResidentRequest
{
    /**
     * معالجة وتحويل التواريخ الواردة بصيغة ISO 8601 إلى صيغة التاريخ المتوافقة مع MySQL وتوحيد رقم الجوال.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];
        $startsAt = $this->input('starts_at');
        $endsAt = $this->input('ends_at');

        if ($startsAt) {
            try {
                $merge['starts_at'] = Carbon::parse($startsAt)->format('Y-m-d H:i:s');
            } catch (\Throwable) {}
        }

        if ($endsAt) {
            try {
                $merge['ends_at'] = Carbon::parse($endsAt)->format('Y-m-d H:i:s');
            } catch (\Throwable) {}
        }

        if ($this->has('visitor_phone')) {
            $merge['visitor_phone'] = SaudiPhone::normalize((string) $this->input('visitor_phone'));
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }

    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'visitor_name' => ['required', 'string', 'min:2', 'max:120'],
            'visitor_phone' => ['required', 'string', 'regex:' . SaudiPhone::REGEX],
            'starts_at' => ['required', 'date', 'after:now'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'visitor_phone.required' => 'رقم هاتف الزائر مطلوب.',
            'visitor_phone.regex' => 'رقم هاتف الزائر يجب أن يبدأ بـ 05 ويتكون من 10 أرقام.',
        ];
    }
}
