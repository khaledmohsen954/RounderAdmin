<?php

namespace App\Modules\Support\Enums;

enum Operation: string
{
    case TicketCreated = 'support.ticket_created';
    case TicketUpdated = 'support.ticket_updated';
}

