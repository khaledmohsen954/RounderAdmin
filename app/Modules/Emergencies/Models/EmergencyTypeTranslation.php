<?php

namespace App\Modules\Emergencies\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات emergency_type_translations وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class EmergencyTypeTranslation extends Model
{
    protected $table = 'emergency_type_translations';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'emergency_type_id',
        'locale',
        'name',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'emergency_type_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر emergency_type_id.
     * @return BelongsTo
     */
    public function emergencyType(): BelongsTo
    {
        return $this->belongsTo(EmergencyType::class, 'emergency_type_id');
    }

}
