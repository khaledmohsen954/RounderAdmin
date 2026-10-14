<?php
namespace App\Modules\Identity\Events;
use App\Modules\Identity\Enums\Operation;
/** حدث مجال خالٍ من تفاصيل HTTP؛ الخدمة تطلقه عبر afterCommit فقط. */
final readonly class IdentityChanged
{
    /**
     * @param Operation $operation العملية التي اكتملت.
     * @param int|null $actorId معرف صاحب العملية عند توفره.
     * @return void
     */
    public function __construct(public Operation $operation, public ?int $actorId) {}
}
