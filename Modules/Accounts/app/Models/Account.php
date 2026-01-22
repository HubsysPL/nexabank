<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\ValueObjects\Money;
use Modules\Core\Enums\AccountStatus;
use App\Models\User;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'balance', // w hubitach
    ];

    protected $casts = [
        'status' => AccountStatus::class,
    ];

    protected $attributes = [
        'balance' => 0,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deposit(Money $amount)
    {
        $this->balance = (new Money($this->balance))->add($amount)->getAmount();
        $this->save();
    }

    public function withdraw(Money $amount)
    {
        $this->balance = (new Money($this->balance))->subtract($amount)->getAmount();
        $this->save();
    }

}
