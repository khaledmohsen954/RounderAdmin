<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_media وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingMedia extends Model
{
    protected $table = 'booking_media';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'uploaded_by',
        'kind',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'uploaded_by' => 'integer',
            'size_bytes' => 'integer',
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
     * السجل المرتبط عبر uploaded_by.
     * @return BelongsTo
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

}
