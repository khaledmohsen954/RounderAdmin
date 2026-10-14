<?php
namespace App\Modules\Catalog\Models;
use App\Modules\Catalog\Enums\FeatureAvailability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/** تفاصيل عرض البند؛ حصص الخدمات القابلة للحجز تبقى في includedServices. */
class PackageFeatureValue extends Model
{
    protected $fillable = ['service_package_id','package_feature_id','availability','value_ar','value_en','value_ur'];
    /** @return array<string, string> */
    protected function casts(): array { return ['availability'=>FeatureAvailability::class]; }
    /** @return BelongsTo */
    public function package(): BelongsTo { return $this->belongsTo(ServicePackage::class,'service_package_id'); }
    /** @return BelongsTo */
    public function feature(): BelongsTo { return $this->belongsTo(PackageFeature::class,'package_feature_id'); }
}
