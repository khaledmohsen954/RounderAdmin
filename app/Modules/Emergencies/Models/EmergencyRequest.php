<?php

namespace App\Modules\Emergencies\Models;

use App\Modules\Properties\Models\Home;
use App\Modules\Providers\Models\ProviderCommissionEntry;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** طلب طوارئ مستقل عن الحجز العادي وله حالات وتاريخ متابعة منفصل. */
class EmergencyRequest extends Model
{
    protected $table = 'emergency_requests';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'resident_id',
        'resident_home_id',
        'home_id',
        'emergency_type_id',
        'status_id',
        'reference',
        'contact_phone',
        'location_details',
        'description',
        'requested_at',
        'dispatched_at',
        'resolved_at',
        'handled_by',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'resident_id' => 'integer',
            'resident_home_id' => 'integer',
            'home_id' => 'integer',
            'emergency_type_id' => 'integer',
            'status_id' => 'integer',
            'requested_at' => 'datetime:Y-m-d H:i:s',
            'dispatched_at' => 'datetime:Y-m-d H:i:s',
            'resolved_at' => 'datetime:Y-m-d H:i:s',
            'handled_by' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر resident_id.
     * @return BelongsTo
     */
    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_id');
    }

    /**
     * السجل المرتبط عبر resident_home_id.
     * @return BelongsTo
     */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /**
     * السجل المرتبط عبر home_id.
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /**
     * السجل المرتبط عبر emergency_type_id.
     * @return BelongsTo
     */
    public function emergencyType(): BelongsTo
    {
        return $this->belongsTo(EmergencyType::class, 'emergency_type_id');
    }

    /**
     * السجل المرتبط عبر status_id.
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequestStatus::class, 'status_id');
    }

    /**
     * السجل المرتبط عبر handled_by.
     * @return BelongsTo
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * السجلات التابعة عبر emergency_request_id.
     * @return HasMany
     */
    public function emergencyRequestUpdates(): HasMany
    {
        return $this->hasMany(EmergencyRequestUpdate::class, 'emergency_request_id');
    }

    /**
     * السجلات التابعة عبر emergency_request_id.
     * @return HasMany
     */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'emergency_request_id');
    }

    /**
     * السجلات التابعة عبر emergency_request_id.
     * @return HasMany
     */
    public function emergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'emergency_request_id');
    }

    /**
     * السجلات التابعة عبر emergency_request_id.
     * @return HasOne
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(EmergencyProviderAssignment::class, 'emergency_request_id');
    }

}
