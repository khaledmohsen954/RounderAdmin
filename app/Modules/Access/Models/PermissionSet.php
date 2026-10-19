<?php
namespace App\Modules\Access\Models;
use App\Modules\Identity\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
/** مجموعة صلاحيات قابلة للإسناد إلى عدة مستخدمي إدارة عبر مفتاح أجنبي. */
class PermissionSet extends Model
{
    protected $fillable = ['name_ar','name_en','permissions'];
    /** @return array<string, string> */
    protected function casts(): array { return ['permissions'=>'array']; }
    /** @return HasMany */
    public function admins(): HasMany { return $this->hasMany(Admin::class); }
}
