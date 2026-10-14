<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات facility_report_media وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class FacilityReportMedia extends Model
{
    protected $table = 'facility_report_media';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'facility_report_id',
        'uploaded_by',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'facility_report_id' => 'integer',
            'uploaded_by' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    /** السجل المرتبط عبر facility_report_id. */
    public function facilityReport(): BelongsTo
    {
        return $this->belongsTo(FacilityReport::class, 'facility_report_id');
    }

    /** السجل المرتبط عبر uploaded_by. */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}
