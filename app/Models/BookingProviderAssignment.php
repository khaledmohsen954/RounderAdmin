<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات booking_provider_assignments وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class BookingProviderAssignment extends Model
{
    protected $table = 'booking_provider_assignments';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'booking_id',
        'provider_profile_id',
        'provider_contract_id',
        'assigned_by',
        'assigned_at',
        'accepted_at',
        'completed_at',
        'provider_note',
    ];

    protected function casts(): array
    {
        return [
            'booking_id' => 'integer',
            'provider_profile_id' => 'integer',
            'provider_contract_id' => 'integer',
            'assigned_by' => 'integer',
            'assigned_at' => 'datetime:Y-m-d H:i:s',
            'accepted_at' => 'datetime:Y-m-d H:i:s',
            'completed_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر booking_id. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /** السجل المرتبط عبر provider_profile_id. */
    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    /** السجل المرتبط عبر provider_contract_id. */
    public function providerContract(): BelongsTo
    {
        return $this->belongsTo(ProviderContract::class, 'provider_contract_id');
    }

    /** السجل المرتبط عبر assigned_by. */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

}
