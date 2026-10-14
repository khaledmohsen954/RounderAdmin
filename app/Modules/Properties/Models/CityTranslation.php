<?php

namespace App\Modules\Properties\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات city_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class CityTranslation extends Model
{
    protected $table = 'city_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'city_id',
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
            'city_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر city_id.
     * @return BelongsTo
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

}
