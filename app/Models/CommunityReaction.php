<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات community_reactions وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class CommunityReaction extends Model
{
    protected $table = 'community_reactions';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'community_post_id',
        'user_id',
        'reaction_code',
    ];

    protected function casts(): array
    {
        return [
            'community_post_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر community_post_id. */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    /** السجل المرتبط عبر user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
