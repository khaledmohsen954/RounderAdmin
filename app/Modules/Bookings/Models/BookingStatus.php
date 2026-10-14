<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Facilities\Models\FacilityReportUpdate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات booking_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingStatus extends Model
{
    protected $table = 'booking_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /**
     * السجلات التابعة عبر booking_status_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(BookingStatusTranslation::class, 'booking_status_id');
    }

    /**
     * السجلات التابعة عبر booking_status_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'booking_status_id');
    }

    /**
     * السجلات التابعة عبر booking_status_id.
     * @return HasMany
     */
    public function bookingStatusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_status_id');
    }

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function facilityReports(): HasMany
    {
        return $this->hasMany(FacilityReport::class, 'status_id');
    }

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function facilityReportUpdates(): HasMany
    {
        return $this->hasMany(FacilityReportUpdate::class, 'status_id');
    }

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function bookingAdditionalWork(): HasMany
    {
        return $this->hasMany(BookingAdditionalWork::class, 'status_id');
    }

}
