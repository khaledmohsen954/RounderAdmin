<?php
namespace App\Modules\Notifications\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Modules\Identity\Models\Admin;
/** Read receipts belong to a single admin and cascade with the account. */
class AdminAlertRead extends Model {
    public $timestamps=false;
    protected $fillable=['admin_id','read_at'];
    /** @return array<string,string> */
    protected function casts(): array { return ['read_at'=>'datetime']; }
    /** @return BelongsTo */
    public function alert(): BelongsTo { return $this->belongsTo(AdminAlert::class,'admin_alert_id'); }
    /** @return BelongsTo */
    public function admin(): BelongsTo { return $this->belongsTo(Admin::class); }
}
