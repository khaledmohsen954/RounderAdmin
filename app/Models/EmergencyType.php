<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات emergency_types وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyType extends Model
{
    protected $table = 'emergency_types';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** السجلات التابعة عبر emergency_type_id. */
    public function translations(): HasMany
    {
        return $this->hasMany(EmergencyTypeTranslation::class, 'emergency_type_id');
    }

    /** السجلات التابعة عبر emergency_type_id. */
    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'emergency_type_id');
    }

}
