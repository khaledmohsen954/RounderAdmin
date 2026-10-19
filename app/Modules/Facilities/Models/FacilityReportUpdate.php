<?php

namespace App\Modules\Facilities\Models;

use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Identity\Models\User;

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
        'admin_updated_by',
        'note',
        'created_at',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facility_report_id' => 'integer',
            'status_id' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s',
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
     * السجل المرتبط عبر status_id.
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }

    /**
     * السجل المرتبط عبر updated_by.
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function adminActor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Identity\Models\Admin::class, 'admin_updated_by');
    }
}
