<?php

namespace Modules\Core\Enums;

enum LedgerStatus: string
{
    case PENDING = 'pending';
    case BOOKED = 'booked';
    case REVERSED = 'reversed';
}
