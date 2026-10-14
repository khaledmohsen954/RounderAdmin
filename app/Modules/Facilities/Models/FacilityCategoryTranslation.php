<?php

namespace App\Modules\Facilities\Models;



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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facility_category_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر facility_category_id.
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(FacilityCategory::class, 'facility_category_id');
    }

}
