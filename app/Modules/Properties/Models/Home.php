<?php

namespace App\Modules\Properties\Models;

use App\Modules\Community\Models\Announcement;
use App\Modules\Finance\Models\AssociationAccount;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Facilities\Models\FacilityReport;
use App\Modules\Community\Models\Initiative;
use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** يمثل بيانات homes وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Home extends Model
{
    protected $table = 'homes';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'city_id',
        'name',
        'address',
        'property_code',
        'health_percent',
        'association_balance',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'city_id' => 'integer',
            'health_percent' => 'integer',
            'association_balance' => 'decimal:2',
        ];
    }

    /**
     * السجل المرتبط عبر city_id.
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function residentHomes(): HasMany
    {
        return $this->hasMany(ResidentHome::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function associationAccounts(): HasMany
    {
        return $this->hasMany(AssociationAccount::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function facilityReports(): HasMany
    {
        return $this->hasMany(FacilityReport::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function communityPosts(): HasMany
    {
        return $this->hasMany(CommunityPost::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function initiatives(): HasMany
    {
        return $this->hasMany(Initiative::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر home_id.
     * @return HasMany
     */
    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'home_id');
    }

    /**
     * السكان المرتبطون بالمبنى مع رقم الوحدة وحالة الوحدة الأساسية.
     * @return BelongsToMany
     */
    public function residents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'resident_homes')
            ->withPivot(['unit_label', 'is_primary'])->withTimestamps();
    }
}
