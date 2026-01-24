<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    protected $casts = [
        'status' => AccountStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }
}
