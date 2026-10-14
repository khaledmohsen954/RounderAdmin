<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return [
            'role_id' => 'integer',
            'email_verified_at' => 'datetime:Y-m-d H:i:s',
            'password' => 'hashed',
        ];
    }

    /** السجل المرتبط عبر role_id. */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /** السجلات التابعة عبر user_id. */
    public function providerProfiles(): HasMany
    {
        return $this->hasMany(ProviderProfile::class, 'user_id');
    }

    /** السجلات التابعة عبر resident_id. */
    public function residentBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resident_id');
    }

    /** السجلات التابعة عبر provider_id. */
    public function providerBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'provider_id');
    }

    /** السجلات التابعة عبر changed_by. */
    public function changedByBookingStatusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class, 'changed_by');
    }

    /** السجلات التابعة عبر user_id. */
    public function associationLedgerEntries(): HasMany
    {
        return $this->hasMany(AssociationLedgerEntry::class, 'user_id');
    }

    /** السجلات التابعة عبر uploaded_by. */
    public function uploadedByFacilityReportMedia(): HasMany
    {
        return $this->hasMany(FacilityReportMedia::class, 'uploaded_by');
    }

    /** السجلات التابعة عبر updated_by. */
    public function updatedByFacilityReportUpdates(): HasMany
    {
        return $this->hasMany(FacilityReportUpdate::class, 'updated_by');
    }

    /** السجلات التابعة عبر created_by. */
    public function createdByResidentVisits(): HasMany
    {
        return $this->hasMany(ResidentVisit::class, 'created_by');
    }

    /** السجلات التابعة عبر created_by. */
    public function createdByAnnouncements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'created_by');
    }

    /** السجلات التابعة عبر created_by. */
    public function createdByCommunityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class, 'created_by');
    }

    /** السجلات التابعة عبر created_by. */
    public function createdByCommunityComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'created_by');
    }

    /** السجلات التابعة عبر user_id. */
    public function communityReactions(): HasMany
    {
        return $this->hasMany(CommunityReaction::class, 'user_id');
    }

    /** السجلات التابعة عبر created_by. */
    public function createdByInitiatives(): HasMany
    {
        return $this->hasMany(Initiative::class, 'created_by');
    }

    /** السجلات التابعة عبر user_id. */
    public function initiativeParticipations(): HasMany
    {
        return $this->hasMany(InitiativeParticipation::class, 'user_id');
    }

    /** السجلات التابعة عبر user_id. */
    public function residentNotifications(): HasMany
    {
        return $this->hasMany(ResidentNotification::class, 'user_id');
    }

    /** السجلات التابعة عبر uploaded_by. */
    public function uploadedByBookingMedia(): HasMany
    {
        return $this->hasMany(BookingMedia::class, 'uploaded_by');
    }

    /** السجلات التابعة عبر provider_id. */
    public function providerBookingAdditionalWork(): HasMany
    {
        return $this->hasMany(BookingAdditionalWork::class, 'provider_id');
    }

    /** السجلات التابعة عبر resident_id. */
    public function residentBookingRatings(): HasMany
    {
        return $this->hasMany(BookingRating::class, 'resident_id');
    }

    /** السجلات التابعة عبر provider_id. */
    public function providerBookingWarranties(): HasMany
    {
        return $this->hasMany(BookingWarranty::class, 'provider_id');
    }

    /** السجلات التابعة عبر resident_id. */
    public function residentEmergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'resident_id');
    }

    /** السجلات التابعة عبر handled_by. */
    public function handledByEmergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'handled_by');
    }

    /** السجلات التابعة عبر updated_by. */
    public function updatedByEmergencyRequestUpdates(): HasMany
    {
        return $this->hasMany(EmergencyRequestUpdate::class, 'updated_by');
    }

    /** السجلات التابعة عبر assigned_by. */
    public function assignedByEmergencyProviderAssignments(): HasMany
    {
        return $this->hasMany(EmergencyProviderAssignment::class, 'assigned_by');
    }

    /** السجلات التابعة عبر assigned_by. */
    public function assignedByBookingProviderAssignments(): HasMany
    {
        return $this->hasMany(BookingProviderAssignment::class, 'assigned_by');
    }

    /** السجلات التابعة عبر resident_id. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'resident_id');
    }

    /** السجلات التابعة عبر user_id. */
    public function memberships(): HasMany
    {
        return $this->hasMany(ResidentHome::class, 'user_id');
    }

    /** الوحدات المرتبطة بالحساب؛ بيانات الوحدة موجودة في pivot وليس في المبنى. */
    public function residentHomes(): BelongsToMany
    {
        return $this->belongsToMany(Home::class, 'resident_homes')
            ->withPivot(['unit_label', 'is_primary'])->withTimestamps();
    }
}
