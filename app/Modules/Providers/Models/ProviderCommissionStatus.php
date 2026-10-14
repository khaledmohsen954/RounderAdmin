<?php

namespace App\Modules\Providers\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات provider_commission_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ProviderCommissionStatus extends Model
{
    protected $table = 'provider_commission_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function providerCommissionEntries(): HasMany
    {
        return $this->hasMany(ProviderCommissionEntry::class, 'status_id');
    }

}
