<?php
namespace App\Core;
use App\Modules\Identity\Models\{Admin, User};
/** اختيار المفتاح الأجنبي الصحيح حسب مصدر الحساب، دون تكرار قواعد التدقيق. */
final class AdminActor
{
    /** @param Admin|User $actor @param string $column @return array<string, int|null> */
    public static function attributes(Admin|User $actor, string $column): array
    {
        return [$column => $actor instanceof User ? $actor->id : null,
            'admin_'.$column => $actor instanceof Admin ? $actor->id : null];
    }
}
