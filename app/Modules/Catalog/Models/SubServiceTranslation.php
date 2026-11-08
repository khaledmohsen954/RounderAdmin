<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات sub_service_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class SubServiceTranslation extends Model
{
    protected $table = 'sub_service_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'sub_service_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * السجل المرتبط عبر sub_service_id.
     * @return BelongsTo
     */
    public function subService(): BelongsTo
    {
        return $this->belongsTo(SubService::class, 'sub_service_id');
    }
}
