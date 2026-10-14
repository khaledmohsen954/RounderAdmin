<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_status_history وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingStatusHistory extends Model
{
    protected $table = 'booking_status_history';

    // سجل تاريخي يحتوي created_at فقط ولا يملك updated_at.
    public const UPDATED_AT = null;

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'booking_status_id',
        'changed_by',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'booking_status_id' => 'integer',
            'changed_by' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر booking_id. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /** السجل المرتبط عبر booking_status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(BookingStatus::class, 'booking_status_id');
    }

    /** السجل المرتبط عبر changed_by. */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

}
