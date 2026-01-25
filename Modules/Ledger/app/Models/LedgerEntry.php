<?php

namespace Modules\Ledger\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\LedgerDirection;
use Modules\Core\Enums\LedgerStatus;
use Modules\Core\ValueObjects\Money;

class LedgerEntry extends Model
{
    protected $fillable = [
        'account_id',
        'amount',
        'direction',
        'status',
        'operation_type',
        'title',
        'counterparty_name',
        'counterparty_account',
        'counterparty_bank',
        'reference_id',
        'booked_at',
    ];

    protected $casts = [
        'amount' => 'int',
        'direction' => LedgerDirection::class,
        'status' => LedgerStatus::class,
        'booked_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function getMoneyAttribute(): Money
    {
        return new Money($this->amount);
    }
}
