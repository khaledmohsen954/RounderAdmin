<?php

namespace App\Modules\Facilities\Models;



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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * السجلات التابعة عبر facility_category_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(FacilityCategoryTranslation::class, 'facility_category_id');
    }

    /**
     * السجلات التابعة عبر facility_category_id.
     * @return HasMany
     */
    public function facilityReports(): HasMany
    {
        return $this->hasMany(FacilityReport::class, 'facility_category_id');
    }

    /**
     * إخفاء عناصر الكتالوج الموقوفة عن الاختيارات الجديدة.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}
