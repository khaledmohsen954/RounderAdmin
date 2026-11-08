<?php

namespace App\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات sub_services وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class SubService extends Model
{
    protected $table = 'sub_services';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'service_id',
        'code',
        'image_path',
        'sort_order',
        'is_active',
        'price_eastern',
        'price_qassim_madinah_kharj'
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'service_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * الخدمة الأم المرتبطة عبر service_id.
     * @return BelongsTo
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    /**
     * السجلات التابعة عبر sub_service_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(SubServiceTranslation::class, 'sub_service_id');
    }

    /**
     * الحجوزات المرتبطة عبر sub_service_id.
     * @return HasMany
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(\App\Modules\Bookings\Models\Booking::class, 'sub_service_id');
    }

    /**
     * أسعار الخدمة الفرعية لمزودي الخدمة في مختلف المدن.
     * @return HasMany
     */
    public function prices(): HasMany
    {
        return $this->hasMany(SubServicePrice::class, 'sub_service_id');
    }

    /**
     * إخفاء الخدمات الفرعية الموقوفة عن الاختيارات الجديدة.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }
}
