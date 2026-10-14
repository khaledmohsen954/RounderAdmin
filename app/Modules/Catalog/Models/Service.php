<?php

namespace App\Modules\Catalog\Models;

use App\Modules\Bookings\Models\Booking;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات services وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Service extends Model
{
    protected $table = 'services';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_category_id',
        'code',
        'base_price',
        'is_active',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_category_id' => 'integer',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * السجل المرتبط عبر service_category_id.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * السجلات التابعة عبر service_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class, 'service_id');
    }

    /**
     * السجلات التابعة عبر service_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_id');
    }

    /**
     * السجلات التابعة عبر service_id.
     * @return HasMany
     */
    public function servicePackageServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_id');
    }

    /**
     * السجلات التابعة عبر service_id.
     * @return HasMany
     */
    public function residentSubscriptionServices(): HasMany
    {
        return $this->hasMany(ResidentSubscriptionService::class, 'service_id');
    }

    /**
     * إخفاء عناصر الكتالوج الموقوفة عن الاختيارات الجديدة.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}
