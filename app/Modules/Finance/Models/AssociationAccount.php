<?php

namespace App\Modules\Finance\Models;

use App\Modules\Properties\Models\Home;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات association_accounts وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class AssociationAccount extends Model
{
    protected $table = 'association_accounts';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'home_id',
        'currency_code',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'home_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر home_id.
     * @return BelongsTo
     */
    public function home(): BelongsTo
    {
        return $this->belongsTo(Home::class, 'home_id');
    }

    /**
     * السجلات التابعة عبر association_account_id.
     * @return HasMany
     */
    public function associationDues(): HasMany
    {
        return $this->hasMany(AssociationDue::class, 'association_account_id');
    }

    /**
     * السجلات التابعة عبر association_account_id.
     * @return HasMany
     */
    public function associationLedgerEntries(): HasMany
    {
        return $this->hasMany(AssociationLedgerEntry::class, 'association_account_id');
    }

}
