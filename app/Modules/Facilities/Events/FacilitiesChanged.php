<?php
namespace App\Modules\Facilities\Events;
use App\Modules\Facilities\Enums\Operation;
/** حدث مجال خالٍ من تفاصيل HTTP؛ الخدمة تطلقه عبر afterCommit فقط. */
final readonly class FacilitiesChanged
{
    /**
     * @param Operation $operation العملية التي اكتملت.
     * @param int|null $actorId معرف صاحب العملية عند توفره.
     * @return void
     */
    public function __construct(public Operation $operation, public ?int $actorId) {}
}
