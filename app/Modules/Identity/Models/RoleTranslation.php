<?php

namespace App\Modules\Identity\Models;



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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر role_id.
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

}
