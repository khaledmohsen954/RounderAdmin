<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** اشتراك وحدة سكنية. price_snapshot مستقل عن تعديلات أسعار الباقات. */
class ResidentSubscription extends Model
{
    protected $table = 'resident_subscriptions';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'resident_home_id',
        'service_package_id',
        'status_id',
        'payment_id',
        'reference',
        'price_snapshot',
        'currency_code',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'resident_home_id' => 'integer',
            'service_package_id' => 'integer',
            'status_id' => 'integer',
            'payment_id' => 'integer',
            'price_snapshot' => 'decimal:2',
            'starts_at' => 'datetime:Y-m-d H:i:s',
            'ends_at' => 'datetime:Y-m-d H:i:s',
            'cancelled_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر resident_home_id. */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /** السجل المرتبط عبر service_package_id. */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(SubscriptionStatus::class, 'status_id');
    }

    /** السجل المرتبط عبر payment_id. */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /** السجلات التابعة عبر resident_subscription_id. */
    public function residentSubscriptionServices(): HasMany
    {
        return $this->hasMany(ResidentSubscriptionService::class, 'resident_subscription_id');
    }

    /** السجلات التابعة عبر resident_subscription_id. */
    public function includedServices(): HasMany
    {
        return $this->hasMany(ResidentSubscriptionService::class, 'resident_subscription_id');
    }

}
