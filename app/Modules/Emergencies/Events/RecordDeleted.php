<?php
namespace App\Modules\Emergencies\Events;
/** إشعار بعد نجاح حذف سجل المجال. */
final readonly class RecordDeleted
{
    /** @param string $model @param int $recordId @param int $adminId */
    public function __construct(public string $model, public int $recordId, public int $adminId) {}
}
