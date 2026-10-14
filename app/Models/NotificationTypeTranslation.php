<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات notification_type_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class NotificationTypeTranslation extends Model
{
    protected $table = 'notification_type_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'notification_type_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'notification_type_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر notification_type_id. */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

}
