<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات community_post_media وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class CommunityPostMedia extends Model
{
    protected $table = 'community_post_media';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'community_post_id',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
    ];

    protected function casts(): array
    {
        return [
            'community_post_id' => 'integer',
            'size_bytes' => 'integer',
        ];
    }

    /** السجل المرتبط عبر community_post_id. */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

}
