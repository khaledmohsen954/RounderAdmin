<?php

namespace App\Modules\Community\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات initiative_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class InitiativeTranslation extends Model
{
    protected $table = 'initiative_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'initiative_id',
        'locale',
        'title',
        'description',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initiative_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر initiative_id.
     * @return BelongsTo
     */
    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class, 'initiative_id');
    }

}
