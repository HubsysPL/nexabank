<?php

namespace Modules\Core\Enums;

enum UserStatus: string
{
    case ACTIVE = 'aktywny';
    case INACTIVE = 'nieaktywny';
    case BANNED = 'zbanowany';
}

