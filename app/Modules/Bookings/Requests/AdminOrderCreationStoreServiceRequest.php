<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\AdminRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

/** التحقق من مدخلات OrderCreationController::storeService قبل تشغيل الخدمة. */
class AdminOrderCreationStoreServiceRequest extends AdminRequest
{
    /**
     * قواعد المدخلات؛ صلاحيات الدور موروثة من قاعدة الطلب.
     * @return array<string, mixed>
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function rules(): array
    {
        return [
            'resident_home_id' => ['required', 'integer', 'exists:resident_homes,id'],
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->where('is_active', true)],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
