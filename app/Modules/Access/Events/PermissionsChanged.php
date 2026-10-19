<?php
namespace App\Modules\Access\Events;
/** حدث بعد اعتماد معاملة الصلاحيات. */
final readonly class PermissionsChanged
{
    /** @param int $permissionSetId @param int $actorId */
    public function __construct(public int $permissionSetId, public int $actorId) {}
}
