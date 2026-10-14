<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات association_ledger_entries وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class AssociationLedgerEntry extends Model
{
    protected $table = 'association_ledger_entries';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'association_account_id',
        'resident_home_id',
        'user_id',
        'payment_id',
        'entry_type',
        'reference',
        'amount',
        'description',
        'posted_at',
    ];

    protected function casts(): array
    {
        return [
            'association_account_id' => 'integer',
            'resident_home_id' => 'integer',
            'user_id' => 'integer',
            'payment_id' => 'integer',
            'amount' => 'decimal:2',
            'posted_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    /** السجل المرتبط عبر association_account_id. */
    public function associationAccount(): BelongsTo
    {
        return $this->belongsTo(AssociationAccount::class, 'association_account_id');
    }

    /** السجل المرتبط عبر resident_home_id. */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /** السجل المرتبط عبر user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** السجل المرتبط عبر payment_id. */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

}
