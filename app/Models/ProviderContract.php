<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** عقد مزود الخدمة ونسب عمولة الطلبات العادية والطوارئ. */
class ProviderContract extends Model
{
    protected $table = 'provider_contracts';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'provider_profile_id',
        'status_id',
        'reference',
        'service_commission_percent',
        'emergency_commission_percent',
        'starts_on',
        'ends_on',
        'terms',
    ];

    protected function casts(): array
    {
        return [
            'provider_profile_id' => 'integer',
            'status_id' => 'integer',
            'service_commission_percent' => 'decimal:2',
            'emergency_commission_percent' => 'decimal:2',
            'starts_on' => 'date:Y-m-d',
            'ends_on' => 'date:Y-m-d',
        ];
    }

    /** السجل المرتبط عبر provider_profile_id. */
    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(ProviderContractStatus::class, 'status_id');
    }

    /** السجلات التابعة عبر provider_contract_id. */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'provider_contract_id');
    }

    /** السجلات التابعة عبر provider_contract_id. */
    public function emergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'provider_contract_id');
    }

    /** السجلات التابعة عبر provider_contract_id. */
    public function bookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'provider_contract_id');
    }

}
