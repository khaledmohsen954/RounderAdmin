<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات emergency_request_status_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyRequestStatusTranslation extends Model
{
    protected $table = 'emergency_request_status_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'emergency_request_status_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'emergency_request_status_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر emergency_request_status_id. */
    public function emergencyRequestStatus(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequestStatus::class, 'emergency_request_status_id');
    }

}
