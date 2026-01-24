<?php

namespace Modules\Core\Enums;

enum TransferStatus: string
{
    case PENDING = 'pending';
    case SENT = 'sent';
    case SETTLED = 'settled';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
}
