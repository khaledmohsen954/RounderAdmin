<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** تعريف الباقة الحالية؛ الاشتراك يحتفظ بنسخة من السعر والخدمات عند الشراء. */
class ServicePackage extends Model
{
    protected $table = 'service_packages';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'billing_cycle_id',
        'code',
        'price',
        'currency_code',
        'duration_months',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'billing_cycle_id' => 'integer',
            'price' => 'decimal:2',
            'duration_months' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر billing_cycle_id. */
    public function billingCycle(): BelongsTo
    {
        return $this->belongsTo(PackageBillingCycle::class, 'billing_cycle_id');
    }

    /** السجلات التابعة عبر service_package_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(ServicePackageTranslation::class, 'service_package_id');
    }

    /** السجلات التابعة عبر service_package_id. */
    public function servicePackageServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_package_id');
    }

    /** السجلات التابعة عبر service_package_id. */
    public function residentSubscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'service_package_id');
    }

    /** السجلات التابعة عبر service_package_id. */
    public function includedServices(): HasMany
    {
        return $this->hasMany(ServicePackageService::class, 'service_package_id');
    }

    /** السجلات التابعة عبر service_package_id. */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'service_package_id');
    }

}
