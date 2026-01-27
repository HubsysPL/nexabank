<?php

namespace Modules\Accounts\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasUuids, HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'id' => 'string',
        'user_id' => 'string',
        'status' => AccountStatus::class,
    ];

    protected $fillable = [
        'user_id',
        'product_code',
        'hrb',
        'bank_code',
        'institution_code',
        'account_sequence',
        'status',
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AccountProduct::class, 'product_code', 'code');
    }
}
