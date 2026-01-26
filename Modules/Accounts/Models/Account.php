<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str; // Import Str facade

class Account extends Model
{
    use HasFactory;

    // Disable auto-incrementing for UUIDs
    public $incrementing = false;

    // Set key type to string for UUIDs
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'account_product_id',
        'account_number',
        'balance',
        'currency',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Generate a UUID for the 'id' if it's not already set
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(AccountProduct::class, 'account_product_id');
    }
}
