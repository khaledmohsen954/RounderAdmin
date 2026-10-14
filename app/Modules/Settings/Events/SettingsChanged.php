<?php
namespace App\Modules\Settings\Events;
use App\Modules\Settings\Enums\Operation;
/** حدث مجال خالٍ من تفاصيل HTTP؛ الخدمة تطلقه عبر afterCommit فقط. */
final readonly class SettingsChanged
{
    /**
     * @param Operation $operation العملية التي اكتملت.
     * @param int|null $actorId معرف صاحب العملية عند توفره.
     * @return void
     */
    public function __construct(public Operation $operation, public ?int $actorId) {}
}
