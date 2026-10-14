<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات subscription_status_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class SubscriptionStatusTranslation extends Model
{
    protected $table = 'subscription_status_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'subscription_status_id',
        'locale',
        'name',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'subscription_status_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر subscription_status_id.
     * @return BelongsTo
     */
    public function subscriptionStatus(): BelongsTo
    {
        return $this->belongsTo(SubscriptionStatus::class, 'subscription_status_id');
    }

}
