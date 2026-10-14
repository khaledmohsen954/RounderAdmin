<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات service_category_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ServiceCategoryTranslation extends Model
{
    protected $table = 'service_category_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_category_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'service_category_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر service_category_id. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

}
