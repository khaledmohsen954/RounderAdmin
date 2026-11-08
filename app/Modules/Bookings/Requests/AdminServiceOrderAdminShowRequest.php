<?php

namespace App\Modules\Bookings\Requests;

use App\Core\Http\Requests\AdminRequest;

/** التحقق من صلاحية عرض تفاصيل حجز الخدمة قبل استدعاء الخدمة. */
class AdminServiceOrderAdminShowRequest extends AdminRequest
{
    /**
     * لا توجد مدخلات استعلام مطلوبة؛ معرّف الحجز يُمرر عبر مسار HTTP.
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
