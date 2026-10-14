<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_status_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingStatusTranslation extends Model
{
    protected $table = 'booking_status_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_status_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'booking_status_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر booking_status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

}
