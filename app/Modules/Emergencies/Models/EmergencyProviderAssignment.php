<?php

namespace App\Modules\Emergencies\Models;

use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Identity\Models\User;

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
        'admin_assigned_by',
        'assigned_at',
        'accepted_at',
        'completed_at',
        'provider_note',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
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

    /**
     * السجل المرتبط عبر emergency_request_id.
     * @return BelongsTo
     */
    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class, 'emergency_request_id');
    }

    /**
     * السجل المرتبط عبر provider_profile_id.
     * @return BelongsTo
     */
    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    /**
     * السجل المرتبط عبر provider_contract_id.
     * @return BelongsTo
     */
    public function providerContract(): BelongsTo
    {
        return $this->belongsTo(ProviderContract::class, 'provider_contract_id');
    }

    /**
     * السجل المرتبط عبر assigned_by.
     * @return BelongsTo
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo */
    public function adminActor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Identity\Models\Admin::class, 'admin_assigned_by');
    }
}
