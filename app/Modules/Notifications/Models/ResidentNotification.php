<?php

namespace App\Modules\Notifications\Models;

use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات resident_notifications وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ResidentNotification extends Model
{
    protected $table = 'resident_notifications';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'user_id',
        'notification_type_id',
        'reference_type',
        'reference_id',
        'title',
        'body',
        'read_at',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'notification_type_id' => 'integer',
            'reference_id' => 'integer',
            'read_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /**
     * السجل المرتبط عبر user_id.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * السجل المرتبط عبر notification_type_id.
     * @return BelongsTo
     */
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

}
