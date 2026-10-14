<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** ربط الباقة بالخدمة وعدد المرات؛ null للكمية يعني غير محدود فقط عند تفعيل is_unlimited. */
class ServicePackageService extends Model
{
    protected $table = 'service_package_services';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_package_id',
        'service_id',
        'included_quantity',
        'is_unlimited',
    ];

    protected function casts(): array
    {
        return [
            'service_package_id' => 'integer',
            'service_id' => 'integer',
            'included_quantity' => 'integer',
            'is_unlimited' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر service_package_id. */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

    /** السجل المرتبط عبر service_id. */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

}
