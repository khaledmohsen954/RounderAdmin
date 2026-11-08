<?php

namespace App\Modules\Support\Events;

use App\Modules\Support\Enums\Operation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SupportTicketsChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Operation $operation,
        public readonly int $ticketId,
        public readonly ?int $actorId = null,
    ) {
    }
}

