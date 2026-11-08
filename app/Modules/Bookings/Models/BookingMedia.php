<?php

namespace App\Modules\Bookings\Models;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** يمثل بيانات booking_media وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingMedia extends Model
{
    protected $table = 'booking_media';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'uploaded_by',
        'admin_uploaded_by',
        'kind',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
    ];

    /**
     * الخصائص المحسوبة المرفقة تلقائياً عند التسلسل.
     */
    protected $appends = [
        'url',
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
            'admin_uploaded_by' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    /**
     * رابط استعراض الوسائط.
     */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
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

    /**
     * السجل المرتبط عبر admin_uploaded_by.
     * @return BelongsTo
     */
    public function adminUploadedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_uploaded_by');
    }
}
