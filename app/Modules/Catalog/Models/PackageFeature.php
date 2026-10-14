<?php
namespace App\Modules\Catalog\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
/** اسم بند المقارنة يمكن تعديله دون تكراره في كل باقة. */
class PackageFeature extends Model
{
    protected $fillable = ['code','name_ar','name_en','name_ur','sort_order','is_active'];
    /** @return array<string, string> */
    protected function casts(): array { return ['sort_order'=>'integer','is_active'=>'boolean']; }
    /** @return HasMany */
    public function values(): HasMany { return $this->hasMany(PackageFeatureValue::class); }
}
