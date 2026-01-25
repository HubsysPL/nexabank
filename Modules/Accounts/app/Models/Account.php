<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'product_code', // Dodano
        'hrb', // Dodano
        'bank_code', // Dodano
        'institution_code', // Dodano
        'account_sequence', // Dodano
        'status',
        'balance', // Cache salda w hubitach
    ];

    protected $casts = [
        'status' => AccountStatus::class,
        'product_code' => 'string', // Dodano
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AccountProduct::class, 'product_code', 'code');
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }
}
