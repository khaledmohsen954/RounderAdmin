<?php

namespace App\Modules\Community\Models;

use App\Modules\Identity\Models\User;

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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'community_post_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر community_post_id.
     * @return BelongsTo
     */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    /**
     * السجل المرتبط عبر user_id.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
