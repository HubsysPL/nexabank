<?php

namespace Modules\Accounts\Models;

use Illuminate\Database\Eloquent\Model;

class AccountProduct extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'description',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];
}
