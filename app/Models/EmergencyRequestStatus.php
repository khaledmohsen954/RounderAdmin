<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات emergency_request_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyRequestStatus extends Model
{
    protected $table = 'emergency_request_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /** السجلات التابعة عبر emergency_request_status_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(EmergencyRequestStatusTranslation::class, 'emergency_request_status_id');
    }

    /** السجلات التابعة عبر status_id. */
    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'status_id');
    }

    /** السجلات التابعة عبر status_id. */
    public function emergencyRequestUpdates(): HasMany
    {
        return $this->hasMany(EmergencyRequestUpdate::class, 'status_id');
    }

}
