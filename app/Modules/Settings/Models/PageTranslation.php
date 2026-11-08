<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * يمثل ترجمة الصفحة الثابتة حسب اللغة.
 */
class PageTranslation extends Model
{
    protected $table = 'page_translations';

    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'content',
    ];

    /**
     * الصفحة المرتبطة بالترجمة.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }
}

