<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Properties\Models\Home;
use App\Modules\Finance\Models\Payment;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** طلب خدمة عادي. total_amount لقطة من السعر وقت الإنشاء ولا يتغير بتعديل الكتالوج. */
class Booking extends Model
{
    protected $table = 'bookings';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'resident_id',
        'home_id',
        'service_id',
        'provider_id',
        'booking_status_id',
        'scheduled_at',
        'total_amount',
        'notes',
        'resident_home_id',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resident_id' => 'integer',
            'home_id' => 'integer',
            'service_id' => 'integer',
            'provider_id' => 'integer',
            'booking_status_id' => 'integer',
            'scheduled_at' => 'datetime',
            'total_amount' => 'decimal:2',
            'resident_home_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر resident_id.
     * @return BelongsTo
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * السجل المرتبط عبر home_id.
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /**
     * السجل المرتبط عبر service_id.
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * السجل المرتبط عبر provider_id.
     * @return BelongsTo
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    /**
     * السجل المرتبط عبر booking_status_id.
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

    /**
     * السجل المرتبط عبر resident_home_id.
     * @return BelongsTo
     */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingStatusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingMedia(): HasMany
    {
        return $this->hasMany(BookingMedia::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingAdditionalWork(): HasMany
    {
        return $this->hasMany(BookingAdditionalWork::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingRatings(): HasMany
    {
        return $this->hasMany(BookingRating::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingWarranties(): HasMany
    {
        return $this->hasMany(BookingWarranty::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function bookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasMany
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'booking_id');
    }

    /**
     * السجلات التابعة عبر booking_id.
     * @return HasOne
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(BookingProviderAssignment::class, 'booking_id');
    }

}
