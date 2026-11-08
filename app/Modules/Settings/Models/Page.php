<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * يمثل صفحة ثابتة في النظام (مثل الشروط والأحكام، سياسة الخصوصية).
 */
class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'slug',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * الترجمات التابعة للصفحة.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'page_id');
    }
}

