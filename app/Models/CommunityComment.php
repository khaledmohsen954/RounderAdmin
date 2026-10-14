<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات community_comments وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class CommunityComment extends Model
{
    protected $table = 'community_comments';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'community_post_id',
        'created_by',
        'parent_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'community_post_id' => 'integer',
            'created_by' => 'integer',
            'parent_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر community_post_id. */
    public function communityPost(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'community_post_id');
    }

    /** السجل المرتبط عبر created_by. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** السجل المرتبط عبر parent_id. */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CommunityComment::class, 'parent_id');
    }

    /** السجلات التابعة عبر parent_id. */
    public function children(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'parent_id');
    }

}
