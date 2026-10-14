<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return [
            'service_category_id' => 'integer',
            'base_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر service_category_id. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /** السجلات التابعة عبر service_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class, 'service_id');
    }

    /** السجلات التابعة عبر service_id. */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'service_id');
    }

    /** السجلات التابعة عبر service_id. */
    public function servicePackageServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_id');
    }

    /** السجلات التابعة عبر service_id. */
    public function residentSubscriptionServices(): HasMany
    {
        return $this->hasMany(ResidentSubscriptionService::class, 'service_id');
    }

}
