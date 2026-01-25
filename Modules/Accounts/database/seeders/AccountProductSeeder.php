<?php

namespace Modules\Accounts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Accounts\Models\AccountProduct;

class AccountProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AccountProduct::create([
            'code' => 'STANDARD',
            'name' => 'Standard Account',
            'description' => 'A basic bank account with standard features.',
            'rules' => json_encode([
                'daily_withdrawal_limit' => 500000, // 5000 HUB in hubits
                'monthly_transfer_limit' => 5000000, // 50000 HUB in hubits
            ]),
        ]);

        AccountProduct::create([
            'code' => 'BONUS100',
            'name' => 'Bonus 100 Account',
            'description' => 'An account that gives 100 HUB bonus on activation.',
            'rules' => json_encode([
                'daily_withdrawal_limit' => 1000000, // 10000 HUB in hubits
                'monthly_transfer_limit' => 10000000, // 100000 HUB in hubits
            ]),
        ]);

        // Add more account products as needed
    }
}
