<?php

namespace App\Modules\Properties\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل ترجمات أسماء المناطق عبر region_id. */
class RegionTranslation extends Model
{
    protected $table = 'region_translations';

    protected $fillable = [
        'region_id',
        'locale',
        'name',
    ];

    protected function casts(): array
    {
        return [
            'region_id' => 'integer',
        ];
    }

    /**
     * المنطقة المرتبطة.
     * @return BelongsTo
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }
}

