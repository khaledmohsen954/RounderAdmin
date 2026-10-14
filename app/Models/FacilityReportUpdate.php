<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات facility_report_updates وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class FacilityReportUpdate extends Model
{
    protected $table = 'facility_report_updates';

    // سجل تاريخي يحتوي created_at فقط ولا يملك updated_at.
    public const UPDATED_AT = null;

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'facility_report_id',
        'status_id',
        'updated_by',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'facility_report_id' => 'integer',
            'status_id' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر facility_report_id. */
    public function facilityReport(): BelongsTo
    {
        return $this->belongsTo(FacilityReport::class, 'facility_report_id');
    }

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }

    /** السجل المرتبط عبر updated_by. */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
