<?php

namespace Modules\Accounts\Services;

use App\Models\User;
use Modules\Accounts\Models\Account;
use Modules\Accounts\Models\AccountProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountService
{
    /**
     * Tworzy nowe konto bankowe dla użytkownika na podstawie wybranego produktu.
     *
     * @param User $user Użytkownik, dla którego tworzone jest konto.
     * @param AccountProduct $product Wybrany produkt konta.
     * @return Account Nowo utworzone konto.
     * @throws \Exception Jeśli wystąpi błąd podczas tworzenia konta.
     */
    public function createAccount(User $user, AccountProduct $product): Account
    {
        return DB::transaction(function () use ($user, $product) {
            // Generowanie unikalnego numeru konta (IBAN)
            $accountNumber = $this->generateUniqueAccountNumber();

            $account = new Account([
                'user_id' => $user->id,
                'account_product_id' => $product->id,
                'account_number' => $accountNumber,
                'balance' => 0, // Początkowe saldo
                'currency' => 'PLN', // Domyślna waluta
                'status' => 'active', // Początkowy status
            ]);
            $account->save();

            return $account;
        });
    }

    /**
     * Generuje unikalny numer konta (IBAN) - symulacja.
     * W prawdziwym systemie byłaby to bardziej złożona logika generowania IBAN.
     *
     * @return string Unikalny numer konta.
     */
    protected function generateUniqueAccountNumber(): string
    {
        // Polska struktura IBAN: PL + 2 cyfry kontrolne + 24 cyfry NRB
        // Dla celów symulacji uproszczone
        do {
            $prefix = 'PL';
            $controlDigits = sprintf('%02d', rand(10, 99));
            $bankCode = '10900004'; // Przykładowy kod banku (BZ WBK/Santander)
            $accountSuffix = sprintf('%016d', rand(0, 9999999999999999)); // 16 cyfr
            $fullAccountNumber = $bankCode . $accountSuffix;

            // Uproszczona suma kontrolna dla symulacji
            // W rzeczywistości algorytm Luhna lub podobny
            $checkSum = sprintf('%02d', rand(0, 99)); 

            $iban = $prefix . $checkSum . $fullAccountNumber;
        } while (Account::where('account_number', $iban)->exists()); // Sprawdź unikalność

        return $iban;
    }
}
