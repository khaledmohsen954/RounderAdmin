<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات service_categories وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ServiceCategory extends Model
{
    protected $table = 'service_categories';

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
     * السجلات التابعة عبر service_category_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceCategoryTranslation::class, 'service_category_id');
    }

    /**
     * السجلات التابعة عبر service_category_id.
     * @return HasMany
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_category_id');
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
