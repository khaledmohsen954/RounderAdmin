<?php
namespace App\Modules\Community\Events;
/** إشعار تعديل محتوى السكان بعد اعتماد المعاملة. */
final readonly class ContentUpdated
{
    /** @param string $model @param int $id @param int $adminId */
    public function __construct(public string $model, public int $id, public int $adminId) {}
}
