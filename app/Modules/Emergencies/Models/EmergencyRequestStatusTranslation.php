<?php

namespace App\Modules\Emergencies\Models;



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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'emergency_request_status_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر emergency_request_status_id.
     * @return BelongsTo
     */
    public function emergencyRequestStatus(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequestStatus::class, 'emergency_request_status_id');
    }

}
