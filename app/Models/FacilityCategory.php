<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات facility_categories وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class FacilityCategory extends Model
{
    protected $table = 'facility_categories';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** السجلات التابعة عبر facility_category_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(FacilityCategoryTranslation::class, 'facility_category_id');
    }

    /** السجلات التابعة عبر facility_category_id. */
    public function facilityReports(): HasMany
    {
        return $this->hasMany(FacilityReport::class, 'facility_category_id');
    }

}
