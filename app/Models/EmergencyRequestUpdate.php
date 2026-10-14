<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات emergency_request_updates وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyRequestUpdate extends Model
{
    protected $table = 'emergency_request_updates';

    // سجل تاريخي يحتوي created_at فقط ولا يملك updated_at.
    public const UPDATED_AT = null;

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'emergency_request_id',
        'status_id',
        'updated_by',
        'note',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'emergency_request_id' => 'integer',
            'status_id' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر emergency_request_id. */
    public function emergencyRequest(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequest::class, 'emergency_request_id');
    }

    /** السجل المرتبط عبر status_id. */
    public function status(): BelongsTo
    {
        return $this->belongsTo(EmergencyRequestStatus::class, 'status_id');
    }

    /** السجل المرتبط عبر updated_by. */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

}
