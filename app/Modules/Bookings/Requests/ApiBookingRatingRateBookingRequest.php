<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\ResidentRequest;

/** التحقق من مدخلات BookingRatingController::rateBooking قبل تشغيل الخدمة. */
class ApiBookingRatingRateBookingRequest extends ResidentRequest
{
    /**
     * تحضير المدخلات قبل التحقق لتسهيل الربط مع تطبيق الجوال.
     */
    protected function prepareForValidation(): void
    {
        $merge = [];

        // دعم rating كمرادف لـ score
        if ($this->has('rating') && !$this->has('score')) {
            $merge['score'] = $this->input('rating');
        }

        // دعم notes كمرادف لـ comment
        if ($this->has('notes') && !$this->has('comment')) {
            $merge['comment'] = $this->input('notes');
        }

        // دعم إرسال tags كسلسلة نصية مفصولة بفواصل
        $tags = $this->input('tags');
        if (is_string($tags)) {
            $merge['tags'] = array_values(array_filter(array_map('trim', explode(',', $tags))));
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
            'score' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:2000'],
            'tags' => ['sometimes', 'nullable', 'array', 'max:10'],
            'tags.*' => ['string', 'max:100'],
        ];
    }
}
