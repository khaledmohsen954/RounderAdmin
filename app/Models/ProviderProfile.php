<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات provider_profiles وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ProviderProfile extends Model
{
    protected $table = 'provider_profiles';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'user_id',
        'company_name',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** السجلات التابعة عبر provider_profile_id. */
    public function providerContracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class, 'provider_profile_id');
    }

    /** السجلات التابعة عبر provider_profile_id. */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'provider_profile_id');
    }

    /** السجلات التابعة عبر provider_profile_id. */
    public function emergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'provider_profile_id');
    }

    /** السجلات التابعة عبر provider_profile_id. */
    public function bookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'provider_profile_id');
    }

    /** السجلات التابعة عبر provider_profile_id. */
    public function contracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class, 'provider_profile_id');
    }

}
