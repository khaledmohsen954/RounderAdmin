<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** تعريف الباقة الحالية؛ الاشتراك يحتفظ بنسخة من السعر والخدمات عند الشراء. */
class ServicePackage extends Model
{
    protected $table = 'service_packages';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $hidden = ['original_price'];

    protected $fillable = [
        'original_price', 'price_before_discount', 'discount_type', 'discount_value', 'image_path',
        'billing_cycle_id',
        'code',
        'price',
        'currency_code',
        'duration_months',
        'is_active',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'price_before_discount' => 'decimal:2',
            'discount_type' => \App\Modules\Catalog\Enums\DiscountType::class,
            'discount_value' => 'decimal:2',
            'billing_cycle_id' => 'integer',
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * السجل المرتبط عبر billing_cycle_id.
     * @return BelongsTo
     */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(PackageBillingCycle::class, 'billing_cycle_id');
    }

    /**
     * السجلات التابعة عبر service_package_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServicePackageTranslation::class, 'service_package_id');
    }

    /**
     * السجلات التابعة عبر service_package_id.
     * @return HasMany
     */
    public function servicePackageServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_package_id');
    }

    /**
     * السجلات التابعة عبر service_package_id.
     * @return HasMany
     */
    public function residentSubscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'service_package_id');
    }

    /**
     * السجلات التابعة عبر service_package_id.
     * @return HasMany
     */
    public function includedServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_package_id');
    }

    /**
     * السجلات التابعة عبر service_package_id.
     * @return HasMany
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'service_package_id');
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
