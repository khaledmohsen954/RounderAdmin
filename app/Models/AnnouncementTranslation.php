<?php

namespace App\Models;

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

    protected function casts(): array
    {
        return [
            'announcement_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر announcement_id. */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class, 'announcement_id');
    }

}
