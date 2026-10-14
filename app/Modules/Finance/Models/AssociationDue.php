<?php

namespace App\Modules\Finance\Models;

use App\Modules\Properties\Models\ResidentHome;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** يمثل بيانات association_dues وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class AssociationDue extends Model
{
    protected $table = 'association_dues';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'association_account_id',
        'resident_home_id',
        'reference',
        'period_start',
        'period_end',
        'amount',
        'due_at',
        'payment_status_id',
    ];

    /**
     * أنواع البيانات المسموح إرجاعها وحفظها عبر Eloquent.
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'association_account_id' => 'integer',
            'resident_home_id' => 'integer',
            'period_start' => 'date:Y-m-d',
            'period_end' => 'date:Y-m-d',
            'amount' => 'decimal:2',
            'due_at' => 'date:Y-m-d',
            'payment_status_id' => 'integer',
        ];
    }

    /**
     * السجل المرتبط عبر association_account_id.
     * @return BelongsTo
     */
    public function associationAccount(): BelongsTo
    {
        return $this->belongsTo(AssociationAccount::class, 'association_account_id');
    }

    /**
     * السجل المرتبط عبر resident_home_id.
     * @return BelongsTo
     */
    public function residentHome(): BelongsTo
    {
        return $this->belongsTo(ResidentHome::class, 'resident_home_id');
    }

    /**
     * السجل المرتبط عبر payment_status_id.
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PaymentStatus::class, 'payment_status_id');
    }

}
