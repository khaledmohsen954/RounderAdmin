<?php

namespace App\Modules\Identity\Models;

use App\Modules\Community\Models\Announcement;
use App\Modules\Finance\Models\AssociationLedgerEntry;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingAdditionalWork;
use App\Modules\Bookings\Models\BookingMedia;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingRating;
use App\Modules\Bookings\Models\BookingStatusHistory;
use App\Modules\Bookings\Models\BookingWarranty;
use App\Modules\Community\Models\CommunityComment;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunityReaction;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestUpdate;
use App\Modules\Facilities\Models\FacilityReportMedia;
use App\Modules\Facilities\Models\FacilityReportUpdate;
use App\Modules\Properties\Models\Home;
use App\Modules\Community\Models\Initiative;
use App\Modules\Community\Models\InitiativeParticipation;
use App\Modules\Providers\Models\ProviderProfile;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Notifications\Models\ResidentNotification;
use App\Modules\Properties\Models\ResidentVisit;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** حسابات الدخول؛ كلمة المرور تُشفّر تلقائياً ولا تُعرض في JSON. */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $hidden = ['password', 'remember_token'];

    protected $table = 'users';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'locale',
        'password',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role_id' => 'integer',
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
            'password' => 'hashed',
        ];
    }

    /**
     * السجل المرتبط عبر role_id.
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function providerProfiles(): HasMany
    {
        return $this->hasMany(ProviderProfile::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر resident_id.
     * @return HasMany
     */
    public function residentBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resident_id');
    }

    /**
     * السجلات التابعة عبر provider_id.
     * @return HasMany
     */
    public function providerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    /**
     * السجلات التابعة عبر changed_by.
     * @return HasMany
     */
    public function changedByBookingStatusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'changed_by');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function associationLedgerEntries(): HasMany
    {
        return $this->hasMany(AssociationLedgerEntry::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر uploaded_by.
     * @return HasMany
     */
    public function uploadedByFacilityReportMedia(): HasMany
    {
        return $this->hasMany(FacilityReportMedia::class, 'uploaded_by');
    }

    /**
     * السجلات التابعة عبر updated_by.
     * @return HasMany
     */
    public function updatedByFacilityReportUpdates(): HasMany
    {
        return $this->hasMany(FacilityReportUpdate::class, 'updated_by');
    }

    /**
     * السجلات التابعة عبر created_by.
     * @return HasMany
     */
    public function createdByResidentVisits(): HasMany
    {
        return $this->hasMany(ResidentVisit::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر created_by.
     * @return HasMany
     */
    public function createdByAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر created_by.
     * @return HasMany
     */
    public function createdByCommunityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر created_by.
     * @return HasMany
     */
    public function createdByCommunityComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function communityReactions(): HasMany
    {
        return $this->hasMany(CommunityReaction::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر created_by.
     * @return HasMany
     */
    public function createdByInitiatives(): HasMany
    {
        return $this->hasMany(Initiative::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function initiativeParticipations(): HasMany
    {
        return $this->hasMany(InitiativeParticipation::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function residentNotifications(): HasMany
    {
        return $this->hasMany(ResidentNotification::class, 'user_id');
    }

    /**
     * السجلات التابعة عبر uploaded_by.
     * @return HasMany
     */
    public function uploadedByBookingMedia(): HasMany
    {
        return $this->hasMany(BookingMedia::class, 'uploaded_by');
    }

    /**
     * السجلات التابعة عبر provider_id.
     * @return HasMany
     */
    public function providerBookingAdditionalWork(): HasMany
    {
        return $this->hasMany(BookingAdditionalWork::class, 'provider_id');
    }

    /**
     * السجلات التابعة عبر resident_id.
     * @return HasMany
     */
    public function residentBookingRatings(): HasMany
    {
        return $this->hasMany(BookingRating::class, 'resident_id');
    }

    /**
     * السجلات التابعة عبر provider_id.
     * @return HasMany
     */
    public function providerBookingWarranties(): HasMany
    {
        return $this->hasMany(BookingWarranty::class, 'provider_id');
    }

    /**
     * السجلات التابعة عبر resident_id.
     * @return HasMany
     */
    public function residentEmergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'resident_id');
    }

    /**
     * السجلات التابعة عبر handled_by.
     * @return HasMany
     */
    public function handledByEmergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'handled_by');
    }

    /**
     * السجلات التابعة عبر updated_by.
     * @return HasMany
     */
    public function updatedByEmergencyRequestUpdates(): HasMany
    {
        return $this->hasMany(EmergencyRequestUpdate::class, 'updated_by');
    }

    /**
     * السجلات التابعة عبر assigned_by.
     * @return HasMany
     */
    public function assignedByEmergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'assigned_by');
    }

    /**
     * السجلات التابعة عبر assigned_by.
     * @return HasMany
     */
    public function assignedByBookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'assigned_by');
    }

    /**
     * السجلات التابعة عبر resident_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resident_id');
    }

    /**
     * السجلات التابعة عبر user_id.
     * @return HasMany
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(ResidentHome::class, 'user_id');
    }

    /**
     * الوحدات المرتبطة بالحساب؛ بيانات الوحدة موجودة في pivot وليس في المبنى.
     * @return BelongsToMany
     */
    public function residentHomes(): BelongsToMany
    {
        return $this->belongsToMany(Home::class, 'resident_homes')
            ->withPivot(['unit_label', 'is_primary'])->withTimestamps();
    }
}
