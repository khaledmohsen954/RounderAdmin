<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات community_posts وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class CommunityPost extends Model
{
    protected $table = 'community_posts';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'home_id',
        'resident_home_id',
        'created_by',
        'reference',
        'body',
        'is_hidden',
    ];

    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
            'resident_home_id' => 'integer',
            'created_by' => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    /** السجل المرتبط عبر home_id. */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /** السجل المرتبط عبر resident_home_id. */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /** السجل المرتبط عبر created_by. */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** السجلات التابعة عبر community_post_id. */
    public function communityPostMedia(): HasMany
    {
        return $this->hasMany(CommunityPostMedia::class, 'community_post_id');
    }

    /** السجلات التابعة عبر community_post_id. */
    public function communityComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'community_post_id');
    }

    /** السجلات التابعة عبر community_post_id. */
    public function communityReactions(): HasMany
    {
        return $this->hasMany(CommunityReaction::class, 'community_post_id');
    }

}
