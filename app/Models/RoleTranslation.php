<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات role_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class RoleTranslation extends Model
{
    protected $table = 'role_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'role_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'role_id' => 'integer',
        ];
    }

    /** السجل المرتبط عبر role_id. */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
