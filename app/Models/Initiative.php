<?php

namespace App\Models;

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

    /** السجلات التابعة عبر initiative_id. */
    public function initiativeParticipations(): HasMany
    {
        return $this->hasMany(InitiativeParticipation::class, 'initiative_id');
    }

    /** السجلات التابعة عبر initiative_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(InitiativeTranslation::class, 'initiative_id');
    }

}
