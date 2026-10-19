<?php
namespace App\Modules\Notifications\Models;
use App\Modules\Notifications\Enums\AlertType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
/** One durable event; no copied personal details or shared read flag. */
class AdminAlert extends Model {
    protected $fillable=['type','subject_id'];
    /** @return array<string,string> */
    protected function casts(): array { return ['type'=>AlertType::class,'subject_id'=>'integer']; }
    /** @return HasMany */
    public function reads(): HasMany { return $this->hasMany(AdminAlertRead::class); }
}
