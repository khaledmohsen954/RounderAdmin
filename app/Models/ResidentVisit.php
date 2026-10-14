<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات resident_visits وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class ResidentVisit extends Model
{
    protected $table = 'resident_visits';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'resident_home_id',
        'created_by',
        'visitor_name',
        'visitor_phone',
        'access_code',
        'starts_at',
        'ends_at',
        'checked_in_at',
        'checked_out_at',
    ];

    protected function casts(): array
    {
        return [
            'resident_home_id' => 'integer',
            'created_by' => 'integer',
            'starts_at' => 'datetime:Y-m-d H:i:s',
            'ends_at' => 'datetime:Y-m-d H:i:s',
            'checked_in_at' => 'datetime:Y-m-d H:i:s',
            'checked_out_at' => 'datetime:Y-m-d H:i:s',
        ];
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

}
