<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Region;
use App\Modules\Providers\Models\ProviderProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * يمثل تسعير الخدمة الفرعية لمزود خدمة في منطقة ومجموعة مدن تابعة له.
 */
class SubServicePrice extends Model
{
    protected $table = 'sub_service_prices';

    protected $fillable = [
        'provider_profile_id',
        'sub_service_id',
        'region_id',
        'price',
        'is_active',
        'approval_status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'provider_profile_id' => 'integer',
            'sub_service_id' => 'integer',
            'region_id' => 'integer',
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'reviewed_by' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    public function provider(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class, 'provider_profile_id');
    }

    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class, 'sub_service_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * المدن التابعة لهذا التسعير.
     *
     * @return BelongsToMany
     */
    public function cities(): BelongsToMany
    {
        return $this->belongsToMany(City::class, 'sub_service_price_cities', 'sub_service_price_id', 'city_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(\App\Modules\Bookings\Models\Booking::class, 'sub_service_price_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
