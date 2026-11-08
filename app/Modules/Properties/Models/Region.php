<?php

namespace App\Modules\Properties\Models;

use App\Modules\Properties\Enums\CountryCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات المناطق وعلاقاتها بالمدن والترجمات. */
class Region extends Model
{
    protected $table = 'regions';

    protected $fillable = [
        'code',
        'country_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'country_code' => CountryCode::class,
        ];
    }

    /**
     * ترجمات اسم المنطقة.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(RegionTranslation::class, 'region_id');
    }

    /**
     * المدن التابعة للمنطقة.
     * @return HasMany
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class, 'region_id');
    }

    /**
     * المناطق النشطة.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * المناطق المتاحة للسوق السعودي.
     * @param Builder $query
     * @return Builder
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('country_code', CountryCode::SaudiArabia->value)->where('is_active', true);
    }
}

