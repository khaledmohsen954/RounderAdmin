<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات notification_types وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class NotificationType extends Model
{
    protected $table = 'notification_types';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /** السجلات التابعة عبر notification_type_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(NotificationTypeTranslation::class, 'notification_type_id');
    }

    /** السجلات التابعة عبر notification_type_id. */
    public function residentNotifications(): HasMany
    {
        return $this->hasMany(ResidentNotification::class, 'notification_type_id');
    }

}
