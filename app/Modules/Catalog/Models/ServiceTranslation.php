<?php

namespace App\Modules\Catalog\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات service_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ServiceTranslation extends Model
{
    protected $table = 'service_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر service_id.
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

}
