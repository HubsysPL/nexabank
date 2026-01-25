<?php

namespace Modules\Transfers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\TransferStatus;
use Modules\Core\Enums\TransferType;

class Transfer extends Model
{
    protected $fillable = [
        'from_account_id',
        'to_account_id',
        'from_account_hrb',
        'to_account_hrb',
        'amount',
        'title',
        'status',
        'type',
    ];

    protected $casts = [
        'status' => TransferStatus::class,
        'type' => TransferType::class,
    ];

    /**
     * Get the account that owns the outgoing transfer.
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the account that owns the incoming transfer.
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
