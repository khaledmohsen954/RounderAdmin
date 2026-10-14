<?php

namespace App\Modules\Community\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات announcement_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class AnnouncementTranslation extends Model
{
    protected $table = 'announcement_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'announcement_id',
        'locale',
        'title',
        'body',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'announcement_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر announcement_id.
     * @return BelongsTo
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

}
