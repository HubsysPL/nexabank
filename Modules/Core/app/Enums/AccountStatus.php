<?php

namespace Modules\Core\Enums;

enum AccountStatus: string
{
    case ACTIVE = 'aktywne';
    case BLOCKED = 'zablokowane';
    case CLOSED = 'zamknięte';
}
