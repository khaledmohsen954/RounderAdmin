<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات facility_category_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class FacilityCategoryTranslation extends Model
{
    protected $table = 'facility_category_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'facility_category_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'facility_category_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر facility_category_id. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

}
