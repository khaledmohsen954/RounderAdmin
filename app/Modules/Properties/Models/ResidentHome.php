<?php

namespace App\Modules\Properties\Models;

use App\Modules\Finance\Models\AssociationDue;
use App\Modules\Finance\Models\AssociationLedgerEntry;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Community\Models\InitiativeParticipation;
use App\Modules\Catalog\Models\ResidentSubscription;
use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** عضوية مستخدم في وحدة محددة؛ يجب التحقق منها قبل الوصول إلى بيانات السكان. */
class ResidentHome extends Model
{
    protected $table = 'resident_homes';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'user_id',
        'home_id',
        'unit_label',
        'is_primary',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'home_id' => 'integer',
            'is_primary' => 'boolean',
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
     * السجل المرتبط عبر home_id.
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function associationDues(): HasMany
    {
        return $this->hasMany(AssociationDue::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function associationLedgerEntries(): HasMany
    {
        return $this->hasMany(AssociationLedgerEntry::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function facilityReports(): HasMany
    {
        return $this->hasMany(FacilityReport::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function residentVisits(): HasMany
    {
        return $this->hasMany(ResidentVisit::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function initiativeParticipations(): HasMany
    {
        return $this->hasMany(InitiativeParticipation::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'resident_home_id');
    }

    /**
     * السجلات التابعة عبر resident_home_id.
     * @return HasMany
     */
    public function residentSubscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'resident_home_id');
    }

}
