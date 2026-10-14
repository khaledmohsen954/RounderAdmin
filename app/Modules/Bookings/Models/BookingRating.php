<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_ratings وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingRating extends Model
{
    protected $table = 'booking_ratings';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'resident_id',
        'score',
        'comment',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'resident_id' => 'integer',
            'score' => 'integer',
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
     * السجل المرتبط عبر resident_id.
     * @return BelongsTo
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

}
