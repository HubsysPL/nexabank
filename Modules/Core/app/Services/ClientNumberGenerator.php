<?php

namespace Modules\Core\Services;

use App\Models\User;

class ClientNumberGenerator
{
    /**
     * Generates a unique, 8-digit numeric Customer ID.
     */
    public static function generate(): int
    {
        do {
            $number = random_int(10000000, 99999999);
        } while (User::where('customer_id', '==',$number)->exists());

        return $number;
    }
}
