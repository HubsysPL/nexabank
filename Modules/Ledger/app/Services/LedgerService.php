<?php

namespace Modules\Ledger\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\LedgerDirection;
use Modules\Core\Enums\LedgerStatus;
use Modules\Core\Exceptions\InsufficientFundsException;
use Modules\Core\ValueObjects\Money;
use Modules\Ledger\Models\LedgerEntry;

class LedgerService
{
    public function bookInternalTransfer(
        Account $fromAccount,
        Account $toAccount,
        Money $amount,
        string $title,
        string $operationType = 'internal_transfer'
    ): void {
        if (!$fromAccount->isActive()) {
            throw new \RuntimeException('Konto nadawcy nie jest aktywne.');
        }

        if (!$toAccount->isActive()) {
            throw new \RuntimeException('Konto odbiorcy nie jest aktywne.');
        }
        
        DB::transaction(function () use ($fromAccount, $toAccount, $amount, $title, $operationType) {
            // Check for insufficient funds
            if ($fromAccount->balance < $amount->getAmount()) {
                throw new InsufficientFundsException('Niewystarczające środki na koncie nadawcy.');
            }

            $referenceId = Str::uuid();

            // Debit fromAccount
            $this->createEntry(
                account: $fromAccount,
                amount: $amount,
                direction: LedgerDirection::DEBIT,
                status: LedgerStatus::BOOKED,
                operationType: $operationType,
                title: $title,
                counterpartyName: $toAccount->user->name ?? 'N/A', // Assuming User has a name
                counterpartyAccount: $toAccount->hrb,
                referenceId: $referenceId
            );

            // Credit toAccount
            $this->createEntry(
                account: $toAccount,
                amount: $amount,
                direction: LedgerDirection::CREDIT,
                status: LedgerStatus::BOOKED,
                operationType: $operationType,
                title: $title,
                counterpartyName: $fromAccount->user->name ?? 'N/A', // Assuming User has a name
                counterpartyAccount: $fromAccount->hrb,
                referenceId: $referenceId
            );

            // Update cached balances
            $fromAccount->decrement('balance', $amount->getAmount());
            $toAccount->increment('balance', $amount->getAmount());
        });
    }

    protected function createEntry(
        Account $account,
        Money $amount,
        LedgerDirection $direction,
        LedgerStatus $status,
        string $operationType,
        string $title,
        ?string $counterpartyName = null,
        ?string $counterpartyAccount = null,
        ?string $counterpartyBank = null,
        ?string $referenceId = null
    ): LedgerEntry {
        return LedgerEntry::create([
            'account_id' => $account->id,
            'amount' => $amount->getAmount(),
            'direction' => $direction,
            'status' => $status,
            'operation_type' => $operationType,
            'title' => $title,
            'counterparty_name' => $counterpartyName,
            'counterparty_account' => $counterpartyAccount,
            'counterparty_bank' => $counterpartyBank,
            'reference_id' => $referenceId,
            'booked_at' => now(),
        ]);
    }

    /**
     * Calculates the current balance for a given account based on ledger entries.
     *
     * @param Account $account
     * @return Money
     */
    public function calculateBalance(Account $account): Money
    {
        $balance = LedgerEntry::where('account_id', $account->id)
            ->where('status', LedgerStatus::BOOKED)
            ->sum(DB::raw("CASE WHEN direction = '" . LedgerDirection::CREDIT->value . "' THEN amount ELSE -amount END"));

        return new Money($balance);
    }
}
