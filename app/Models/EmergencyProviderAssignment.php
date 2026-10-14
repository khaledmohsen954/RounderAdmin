<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات emergency_provider_assignments وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyProviderAssignment extends Model
{
    protected $table = 'emergency_provider_assignments';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'emergency_request_id',
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
            'emergency_request_id' => 'integer',
            'provider_profile_id' => 'integer',
            'provider_contract_id' => 'integer',
            'assigned_by' => 'integer',
            'assigned_at' => 'datetime:Y-m-d H:i:s',
            'accepted_at' => 'datetime:Y-m-d H:i:s',
            'completed_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر emergency_request_id. */
    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class, 'emergency_request_id');
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
