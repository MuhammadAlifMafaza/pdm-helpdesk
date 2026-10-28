<?php

namespace App\Models\Modules\Perbaikan\Enums;

enum EventType: string
{
    case CREATE = 'CREATE';
    case ASSIGN = 'ASSIGN';
    case COMPLETE = 'COMPLETE';
    case REJECT = 'REJECT';
    case REOPEN = 'REOPEN';
    case CHAT = 'CHAT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case PENDING = 'PENDING';
    case SYSTEM = 'SYSTEM';
}
