<?php

namespace Modules\Core\Enums;

enum AccountStatus: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';
    case CLOSED = 'closed';
}
