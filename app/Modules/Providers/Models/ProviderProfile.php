<?php

namespace App\Modules\Providers\Models;

use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Identity\Models\User;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubServicePrice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات provider_profiles وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ProviderProfile extends Model
{
    protected $table = 'provider_profiles';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'user_id',
        'company_name',
        'trade_name',
        'provider_type',
        'years_of_experience',
        'commercial_registration',
        'legal_representative',
        'legal_representative_id',
        'company_seal',
        'commercial_registration_document',
        'legal_representative_id_document',
        'signed_annex_a',
        'municipal_license',
        'contract_accepted_at',
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
            'years_of_experience' => 'integer',
            'contract_accepted_at' => 'datetime',
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
     * الخدمات التي يقدمها مزود الخدمة.
     * @return BelongsToMany
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'provider_services', 'provider_profile_id', 'service_id')
            ->withTimestamps();
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
     * أسعار الخدمات الفرعية لمزود الخدمة.
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(SubServicePrice::class, 'provider_profile_id');
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
