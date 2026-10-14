<?php

namespace App\Modules\Emergencies\Models;



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

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * السجلات التابعة عبر emergency_type_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(EmergencyTypeTranslation::class, 'emergency_type_id');
    }

    /**
     * السجلات التابعة عبر emergency_type_id.
     * @return HasMany
     */
    public function emergencyRequests(): HasMany
    {
        return $this->hasMany(EmergencyRequest::class, 'emergency_type_id');
    }

    /**
     * إخفاء عناصر الكتالوج الموقوفة عن الاختيارات الجديدة.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}
