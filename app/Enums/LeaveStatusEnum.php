<?php

namespace App\Enums;

enum LeaveStatusEnum: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
