<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات initiative_participations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class InitiativeParticipation extends Model
{
    protected $table = 'initiative_participations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'initiative_id',
        'resident_home_id',
        'user_id',
        'pledged_amount',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'initiative_id' => 'integer',
            'resident_home_id' => 'integer',
            'user_id' => 'integer',
            'pledged_amount' => 'decimal:2',
            'joined_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر initiative_id. */
    public function initiative(): BelongsTo
    {
        return $this->belongsTo(Initiative::class, 'initiative_id');
    }

    /** السجل المرتبط عبر resident_home_id. */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /** السجل المرتبط عبر user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
