<?php
namespace App\Modules\Identity\Models;
use App\Modules\Access\Models\PermissionSet;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/** حساب الإدارة منفصل عن حسابات السكان ومزودي الخدمة؛ لا يصدر رموز تطبيق. */
class Admin extends Authenticatable
{
    protected $table = 'admins';
    protected $fillable = ['name','email','phone','locale','password','permission_set_id','is_active','is_super_admin'];
    protected $hidden = ['password','remember_token'];
    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['password'=>'hashed','is_active'=>'boolean','is_super_admin'=>'boolean','permission_set_id'=>'integer'];
    }
    /** @return BelongsTo */
    public function permissionSet(): BelongsTo
    {
        return $this->belongsTo(PermissionSet::class);
    }
}
