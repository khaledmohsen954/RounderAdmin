<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات cities وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class City extends Model
{
    protected $table = 'cities';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /** السجلات التابعة عبر city_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class, 'city_id');
    }

    /** السجلات التابعة عبر city_id. */
    public function homes(): HasMany
    {
        return $this->hasMany(Home::class, 'city_id');
    }

}
