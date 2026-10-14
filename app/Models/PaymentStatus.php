<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** يمثل بيانات payment_statuses وعلاقاتها بالمفاتيح الأجنبية في قاعدة البيانات. */
class PaymentStatus extends Model
{
    protected $table = 'payment_statuses';

    /** الحقول المسموح حفظها؛ يجب تمرير البيانات المتحقق منها فقط من طبقة الطلب. */
    protected $fillable = [
        'code',
    ];

    /** السجلات التابعة عبر payment_status_id. */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payment_status_id');
    }

    /** السجلات التابعة عبر payment_status_id. */
    public function associationDues(): HasMany
    {
        return $this->hasMany(AssociationDue::class, 'payment_status_id');
    }

}
