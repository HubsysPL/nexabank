<?php

namespace Modules\Core\Services;

use App\Models\User; // Assuming App\Models\User
use Illuminate\Support\Str;

class ClientNumberGenerator
{
    public static function generate(): string
    {
        do {
            $number = (string) random_int(10000000, 99999999); // 8-digit customer_id
        } while (User::where('customer_id', $number)->exists());

        return $number;
    }
}