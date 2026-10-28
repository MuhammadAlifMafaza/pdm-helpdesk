<?php

namespace App\Models\Modules\Perbaikan\Enums;

enum TicketStatus: string
{
    case OPEN = 'Open';
    case IN_PROGRESS = 'In Progress';
    case CLOSE = 'Close';
}
