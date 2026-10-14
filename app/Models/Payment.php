<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات payments وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Payment extends Model
{
    protected $table = 'payments';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'payment_status_id',
        'amount',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'payment_status_id' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    /** السجل المرتبط عبر booking_id. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /** السجل المرتبط عبر payment_status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

    /** السجلات التابعة عبر payment_id. */
    public function associationLedgerEntries(): HasMany
    {
        return $this->hasMany(AssociationLedgerEntry::class, 'payment_id');
    }

    /** السجلات التابعة عبر payment_id. */
    public function residentSubscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'payment_id');
    }

}
