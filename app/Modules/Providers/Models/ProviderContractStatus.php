<?php

namespace App\Modules\Providers\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات provider_contract_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ProviderContractStatus extends Model
{
    protected $table = 'provider_contract_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /**
     * السجلات التابعة عبر status_id.
     * @return HasMany
     */
    public function providerContracts(): HasMany
    {
        return $this->hasMany(ProviderContract::class, 'status_id');
    }

}
