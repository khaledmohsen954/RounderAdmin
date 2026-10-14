<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات subscription_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class SubscriptionStatus extends Model
{
    protected $table = 'subscription_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /**
     * السجلات التابعة عبر subscription_status_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SubscriptionStatusTranslation::class, 'subscription_status_id');
    }

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function residentSubscriptions(): HasMany
    {
        return $this->hasMany(ResidentSubscription::class, 'status_id');
    }

}
