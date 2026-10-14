<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** حصص الخدمات المنسوخة عند الاشتراك؛ used_quantity يسجل الاستهلاك. */
class ResidentSubscriptionService extends Model
{
    protected $table = 'resident_subscription_services';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'resident_subscription_id',
        'service_id',
        'included_quantity',
        'used_quantity',
        'is_unlimited',
    ];

    protected function casts(): array
    {
        return [
            'resident_subscription_id' => 'integer',
            'service_id' => 'integer',
            'included_quantity' => 'integer',
            'used_quantity' => 'integer',
            'is_unlimited' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر resident_subscription_id. */
    public function residentSubscription(): BelongsTo
    {
        return $this->belongsTo(ResidentSubscription::class, 'resident_subscription_id');
    }

    /** السجل المرتبط عبر service_id. */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

}
