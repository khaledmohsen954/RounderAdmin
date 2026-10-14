<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_additional_work وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingAdditionalWork extends Model
{
    protected $table = 'booking_additional_work';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'provider_id',
        'reference',
        'description',
        'amount',
        'status_id',
        'approved_at',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'provider_id' => 'integer',
            'amount' => 'decimal:2',
            'status_id' => 'integer',
            'approved_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /**
     * السجل المرتبط عبر booking_id.
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
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
     * السجل المرتبط عبر status_id.
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'status_id');
    }

}
