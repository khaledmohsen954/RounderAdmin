<?php
namespace App\Modules\Catalog\Events;
use App\Modules\Catalog\Enums\Operation;
/** حدث مجال خالٍ من تفاصيل HTTP؛ الخدمة تطلقه عبر afterCommit فقط. */
final readonly class CatalogChanged
{
    /**
     * @param Operation $operation العملية التي اكتملت.
     * @param int|null $actorId معرف صاحب العملية عند توفره.
     * @return void
     */
    public function __construct(public Operation $operation, public ?int $actorId) {}
}
