<?php

namespace App\Modules\Properties\Models;



use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات cities وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class City extends Model
{
    protected $table = 'cities';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code', 'country_code', 'source_city_id', 'region_id', 'is_active',
    ];

    /**
     * المنطقة المرتبطة بالمدينة.
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * السجلات التابعة عبر city_id.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(CityTranslation::class, 'city_id');
    }

    /**
     * السجلات التابعة عبر city_id.
     * @return HasMany
     */
    public function homes(): HasMany
    {
        return $this->hasMany(Home::class, 'city_id');
    }

    public function subServicePrices(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Modules\Catalog\Models\SubServicePrice::class, 'sub_service_price_cities', 'city_id', 'sub_service_price_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'source_city_id' => 'integer',
            'region_id' => 'integer',
            'country_code' => \App\Modules\Properties\Enums\CountryCode::class,
        ];
    }
    /**
     * المدن المتاحة للعقارات الجديدة في السوق السعودي.
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('country_code', \App\Modules\Properties\Enums\CountryCode::SaudiArabia->value)->where('is_active', true);
    }
}
