<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات announcements وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Announcement extends Model
{
    protected $table = 'announcements';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'home_id',
        'created_by',
        'reference',
        'published_at',
        'expires_at',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
            'created_by' => 'integer',
            'published_at' => 'datetime:Y-m-d H:i:s',
            'expires_at' => 'datetime:Y-m-d H:i:s',
            'is_published' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر home_id. */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /** السجل المرتبط عبر created_by. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** السجلات التابعة عبر announcement_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(AnnouncementTranslation::class, 'announcement_id');
    }

}
