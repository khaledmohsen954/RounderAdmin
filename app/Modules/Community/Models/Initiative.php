<?php

namespace App\Modules\Community\Models;

use App\Modules\Properties\Models\Home;
use App\Modules\Identity\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات initiatives وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Initiative extends Model
{
    protected $table = 'initiatives';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'home_id',
        'created_by',
        'reference',
        'title',
        'description',
        'target_amount',
        'starts_at',
        'ends_at',
        'is_published',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
            'created_by' => 'integer',
            'target_amount' => 'decimal:2',
            'starts_at' => 'datetime:Y-m-d H:i:s',
            'ends_at' => 'datetime:Y-m-d H:i:s',
            'is_published' => 'boolean',
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
     * السجل المرتبط عبر created_by.
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * السجلات التابعة عبر initiative_id.
     * @return HasMany
     */
    public function initiativeParticipations(): HasMany
    {
        return $this->hasMany(InitiativeParticipation::class, 'initiative_id');
    }

    /**
     * السجلات التابعة عبر initiative_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(InitiativeTranslation::class, 'initiative_id');
    }

}
