<?php

namespace App\Modules\Community\Models;

use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Identity\Models\User;

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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
            'resident_home_id' => 'integer',
            'created_by' => 'integer',
            'is_hidden' => 'boolean',
        ];
    }

    /**
     * السجل المرتبط عبر home_id.
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /**
     * السجل المرتبط عبر resident_home_id.
     * @return BelongsTo
     */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /**
     * السجل المرتبط عبر created_by.
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر community_post_id.
     * @return HasMany
     */
    public function communityPostMedia(): HasMany
    {
        return $this->hasMany(CommunityPostMedia::class, 'community_post_id');
    }

    /**
     * السجلات التابعة عبر community_post_id.
     * @return HasMany
     */
    public function communityComments(): HasMany
    {
        return $this->hasMany(CommunityComment::class, 'community_post_id');
    }

    /**
     * السجلات التابعة عبر community_post_id.
     * @return HasMany
     */
    public function communityReactions(): HasMany
    {
        return $this->hasMany(CommunityReaction::class, 'community_post_id');
    }

}
