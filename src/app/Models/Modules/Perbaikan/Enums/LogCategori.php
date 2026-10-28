<?php

namespace App\Models\Modules\Perbaikan\Enums;

enum LogCategori: string
{
    case STATUS = 'Status';
    case CHAT = 'Chat';
    case UPDATE = 'Update Data';
    case DELETE = 'Delete Data';
    case PENDING = 'Pending';

}
