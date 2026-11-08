<?php

namespace App\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * يمثل إعدادات وبيانات المنشأة الرسمية (الاسم التجاري، السجل، الضريبة، الختم، الممثل القانوني).
 */
class CompanySetting extends Model
{
    protected $table = 'company_settings';

    protected $fillable = [
        'trade_name',
        'cr_number',
        'tax_number',
        'trademark_registration',
        'legal_representative',
        'representative_title',
        'stamp_path',
        'support_phone',
        'support_email',
        'support_working_hours',
        'support_chat_enabled',
    ];

    protected function casts(): array
    {
        return [
            'support_chat_enabled' => 'boolean',
        ];
    }

    /**
     * جلب سجل إعدادات المنشأة الحالي أو إنشاء كائن جديد فارغ.
     *
     * @return static
     */
    public static function current(): static
    {
        return static::query()->first() ?? new static([
            'trade_name' => 'شركة راوندر لخدمات التشغيل والصيانة',
            'cr_number' => '',
            'tax_number' => null,
            'trademark_registration' => null,
            'legal_representative' => '',
            'representative_title' => 'المدير العام',
            'stamp_path' => null,
            'support_phone' => '920000488',
            'support_email' => 'support@rounder.sa',
            'support_working_hours' => 'الأحد إلى الخميس، 9 ص - 6 م',
            'support_chat_enabled' => true,
        ]);
    }

    /**
     * رابط صورة الختم الرسمي إن وجدت.
     *
     * @return string|null
     */
    public function getStampUrlAttribute(): ?string
    {
        if (!$this->stamp_path) {
            return null;
        }

        return Storage::disk('public')->url($this->stamp_path);
    }
}

