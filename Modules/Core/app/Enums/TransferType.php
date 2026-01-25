<?php

namespace Modules\Core\Enums;

enum TransferType: string
{
    case INTERNAL = 'internal';
    case OUTGOING = 'outgoing';
    case INCOMING = 'incoming';
}
