<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات facility_reports وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class FacilityReport extends Model
{
    protected $table = 'facility_reports';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'home_id',
        'resident_home_id',
        'facility_category_id',
        'status_id',
        'reference',
        'title',
        'description',
        'location',
        'reported_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
            'resident_home_id' => 'integer',
            'facility_category_id' => 'integer',
            'status_id' => 'integer',
            'reported_at' => 'datetime:Y-m-d H:i:s',
            'resolved_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر home_id. */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /** السجل المرتبط عبر resident_home_id. */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /** السجل المرتبط عبر facility_category_id. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }

    /** السجلات التابعة عبر facility_report_id. */
    public function facilityReportMedia(): HasMany
    {
        return $this->hasMany(FacilityReportMedia::class, 'facility_report_id');
    }

    /** السجلات التابعة عبر facility_report_id. */
    public function facilityReportUpdates(): HasMany
    {
        return $this->hasMany(FacilityReportUpdate::class, 'facility_report_id');
    }

}
