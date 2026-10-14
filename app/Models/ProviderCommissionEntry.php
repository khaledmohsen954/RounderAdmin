<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** قيد عمولة يحتفظ بنسبة العقد وقت الاحتساب لتجنب تغيير المستحقات السابقة. */
class ProviderCommissionEntry extends Model
{
    protected $table = 'provider_commission_entries';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'provider_profile_id',
        'provider_contract_id',
        'status_id',
        'booking_id',
        'emergency_request_id',
        'gross_amount',
        'commission_percent_snapshot',
        'commission_amount',
        'calculated_at',
        'settled_at',
    ];

    protected function casts(): array
    {
        return [
            'provider_profile_id' => 'integer',
            'provider_contract_id' => 'integer',
            'status_id' => 'integer',
            'booking_id' => 'integer',
            'emergency_request_id' => 'integer',
            'gross_amount' => 'decimal:2',
            'commission_percent_snapshot' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'calculated_at' => 'datetime:Y-m-d H:i:s',
            'settled_at' => 'datetime:Y-m-d H:i:s',
        ];
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

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProviderCommissionStatus::class, 'status_id');
    }

    /** السجل المرتبط عبر booking_id. */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /** السجل المرتبط عبر emergency_request_id. */
    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class, 'emergency_request_id');
    }

}
