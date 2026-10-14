<?php

namespace App\Modules\Providers\Models;

use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Identity\Models\User;

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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * السجل المرتبط عبر user_id.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر provider_profile_id.
     * @return HasMany
     */
    public function providerContracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class, 'provider_profile_id');
    }

    /**
     * السجلات التابعة عبر provider_profile_id.
     * @return HasMany
     */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'provider_profile_id');
    }

    /**
     * السجلات التابعة عبر provider_profile_id.
     * @return HasMany
     */
    public function emergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'provider_profile_id');
    }

    /**
     * السجلات التابعة عبر provider_profile_id.
     * @return HasMany
     */
    public function bookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'provider_profile_id');
    }

    /**
     * السجلات التابعة عبر provider_profile_id.
     * @return HasMany
     */
    public function contracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class, 'provider_profile_id');
    }

}
