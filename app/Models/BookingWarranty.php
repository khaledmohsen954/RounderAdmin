<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_warranties وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingWarranty extends Model
{
    protected $table = 'booking_warranties';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'provider_id',
        'reference',
        'terms',
        'starts_on',
        'ends_on',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'provider_id' => 'integer',
            'starts_on' => 'date:Y-m-d',
            'ends_on' => 'date:Y-m-d',
        ];
    }

    /** السجل المرتبط عبر booking_id. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /** السجل المرتبط عبر provider_id. */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

}
