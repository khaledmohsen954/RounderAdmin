<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات package_billing_cycles وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class PackageBillingCycle extends Model
{
    protected $table = 'package_billing_cycles';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
        'months',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'months' => 'integer',
        ];
    }

    /**
     * السجلات التابعة عبر billing_cycle_id.
     * @return HasMany
     */
    public function servicePackages(): HasMany
    {
        return $this->hasMany(ServicePackage::class, 'billing_cycle_id');
    }

}
