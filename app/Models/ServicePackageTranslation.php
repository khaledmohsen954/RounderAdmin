<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات service_package_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ServicePackageTranslation extends Model
{
    protected $table = 'service_package_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_package_id',
        'locale',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'service_package_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر service_package_id. */
    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'service_package_id');
    }

}
