<?php

namespace Modules\Core\Enums;

enum LedgerDirection: string
{
    case DEBIT = 'debit';
    case CREDIT = 'credit';

    public function isCredit(): bool
    {
        return $this === self::CREDIT;
    }

    public function isDebit(): bool
    {
        return $this === self::DEBIT;
    }
}
