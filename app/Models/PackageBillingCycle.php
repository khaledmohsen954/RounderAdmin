<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return [
            'months' => 'integer',
        ];
    }

    /** السجلات التابعة عبر billing_cycle_id. */
    public function servicePackages(): HasMany
    {
        return $this->hasMany(ServicePackage::class, 'billing_cycle_id');
    }

}
