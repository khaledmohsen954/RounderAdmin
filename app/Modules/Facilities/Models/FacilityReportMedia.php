<?php

namespace App\Modules\Facilities\Models;

use App\Modules\Identity\Models\User;

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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facility_report_id' => 'integer',
            'uploaded_by' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر facility_report_id.
     * @return BelongsTo
     */
    public function facilityReport(): BelongsTo
    {
        return $this->belongsTo(FacilityReport::class, 'facility_report_id');
    }

    /**
     * السجل المرتبط عبر uploaded_by.
     * @return BelongsTo
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}
