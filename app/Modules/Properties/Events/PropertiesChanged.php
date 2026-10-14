<?php
namespace App\Modules\Properties\Events;
use App\Modules\Properties\Enums\Operation;
/** حدث مجال خالٍ من تفاصيل HTTP؛ الخدمة تطلقه عبر afterCommit فقط. */
final readonly class PropertiesChanged
{
    /**
     * @param Operation $operation العملية التي اكتملت.
     * @param int|null $actorId معرف صاحب العملية عند توفره.
     * @return void
     */
    public function __construct(public Operation $operation, public ?int $actorId) {}
}
