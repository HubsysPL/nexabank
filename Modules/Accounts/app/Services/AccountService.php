<?php

namespace Modules\Accounts\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Dodano
use Modules\Accounts\Models\Account;
use Modules\Accounts\Models\AccountProduct;
use Modules\Core\Services\HrbService;
use Modules\Core\Enums\AccountStatus;

class AccountService
{
    public function createAccount(User $user, AccountProduct $product): Account
    {
        return DB::transaction(function () use ($user, $product) {
            // Placeholder for real bank code and institution code
            // In a real scenario, these would likely come from configuration or a more complex system.
            $bankCode = '1090'; // Example: Alior Bank's old code
            $institutionCode = '0001'; // Example institution code

            // Generate a unique 12-digit account sequence
            $accountSequence = str_pad(mt_rand(0, 999999999999), 12, '0', STR_PAD_LEFT);

            $hrb = HrbService::generate($bankCode, $institutionCode, $accountSequence);

            $account = Account::create([
                'id' => Str::uuid(), // Dodano generowanie UUID
                'user_id' => $user->id,
                'account_product_code' => $product->code,
                'hrb' => $hrb,
                'bank_code' => $bankCode,
                'institution_code' => $institutionCode,
                'account_sequence' => $accountSequence,
                'status' => AccountStatus::ACTIVE,
                'balance' => 0, // Initial balance is 0
            ]);

            return $account;
        });
    }
}
