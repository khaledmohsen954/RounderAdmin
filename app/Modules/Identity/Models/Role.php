<?php

namespace App\Modules\Identity\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات roles وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class Role extends Model
{
    protected $table = 'roles';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /**
     * السجلات التابعة عبر role_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(RoleTranslation::class, 'role_id');
    }

    /**
     * السجلات التابعة عبر role_id.
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

}
