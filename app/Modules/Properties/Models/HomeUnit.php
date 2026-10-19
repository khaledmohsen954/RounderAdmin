<?php
namespace App\Modules\Properties\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
/** شقة مادية واحدة؛ عضويات السكان ترتبط بها دون تكرار المساحة والغرف. */
class HomeUnit extends Model
{
    protected $fillable = ['home_id','unit_number','floor_number','area_sqm','rooms_count'];
    /** @return array<string,string> */
    protected function casts(): array { return ['home_id'=>'integer','floor_number'=>'integer','area_sqm'=>'decimal:2','rooms_count'=>'integer']; }
    /** @return BelongsTo */
    public function home(): BelongsTo { return $this->belongsTo(Home::class); }
    /** @return HasMany */
    public function memberships(): HasMany { return $this->hasMany(ResidentHome::class); }
}
