<?php

namespace Modules\Clearing\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\ClearingBatchStatus;

class ClearingBatch extends Model
{
    protected $fillable = [
        'status',
        'processed_transfers_count',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'status' => ClearingBatchStatus::class,
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
