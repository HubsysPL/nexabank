<?php

namespace Modules\Transfers\Services;

use Illuminate\Support\Facades\DB;
use Modules\Accounts\Models\Account;
use Modules\Core\Enums\LedgerDirection;
use Modules\Core\Enums\LedgerStatus;
use Modules\Core\Enums\TransferStatus;
use Modules\Core\Enums\TransferType;
use Modules\Core\Exceptions\InsufficientFundsException;
use Modules\Core\ValueObjects\Money;
use Modules\Ledger\Services\LedgerService;
use Modules\Transfers\Models\Transfer;

class TransferService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    /**
     * Inicjuje przelew wewnętrzny między dwoma kontami w Nexa Bank.
     */
    public function initiateInternalTransfer(
        Account $fromAccount,
        Account $toAccount,
        Money $amount,
        string $title
    ): Transfer {
        if (!$fromAccount->isActive()) {
            throw new \RuntimeException('Konto nadawcy nie jest aktywne.');
        }

        if (!$toAccount->isActive()) {
            throw new \RuntimeException('Konto odbiorcy nie jest aktywne.');
        }

        if ($fromAccount->balance < $amount->getAmount()) {
            throw new InsufficientFundsException('Niewystarczające środki na koncie nadawcy.');
        }

        return DB::transaction(function () use ($fromAccount, $toAccount, $amount, $title) {
            // 1. Utwórz rekord przelewu
            $transfer = Transfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => $toAccount->id,
                'from_account_hrb' => $fromAccount->hrb,
                'to_account_hrb' => $toAccount->hrb,
                'amount' => $amount->getAmount(),
                'title' => $title,
                'status' => TransferStatus::BOOKED, // Bezpośrednio zaksięgowany
                'type' => TransferType::INTERNAL,
            ]);

            // 2. Zaksięguj przelew w LedgerService
            $this->ledgerService->bookInternalTransfer(
                fromAccount: $fromAccount,
                toAccount: $toAccount,
                amount: $amount,
                title: $title,
                operationType: 'internal_transfer'
            );

            return $transfer;
        });
    }

    /**
     * Inicjuje przelew wychodzący do innego banku (lub wewnętrzny, jeśli to_account_hrb jest naszym kontem).
     * Rezerwuje środki i tworzy wpisy w Ledgerze ze statusem PENDING.
     */
    public function initiateOutgoingTransfer(
        Account $fromAccount,
        string $toAccountHrb,
        Money $amount,
        string $title
    ): Transfer {
        if (!$fromAccount->isActive()) {
            throw new \RuntimeException('Konto nadawcy nie jest aktywne.');
        }

        if ($fromAccount->balance < $amount->getAmount()) {
            throw new InsufficientFundsException('Niewystarczające środki na koncie nadawcy.');
        }

        return DB::transaction(function () use ($fromAccount, $toAccountHrb, $amount, $title) {
            // 1. Utwórz rekord przelewu
            $transfer = Transfer::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id' => null, // Docelowe konto jest zewnętrzne
                'from_account_hrb' => $fromAccount->hrb,
                'to_account_hrb' => $toAccountHrb,
                'amount' => $amount->getAmount(),
                'title' => $title,
                'status' => TransferStatus::PENDING, // Oczekuje na clearing
                'type' => TransferType::OUTGOING,
            ]);

            // 2. Utwórz wpisy w Ledgerze ze statusem PENDING
            // Tutaj symulujemy rezerwację środków, zanim przelew zostanie faktycznie wysłany i zaksięgowany
            $this->ledgerService->createEntry(
                account: $fromAccount,
                amount: $amount,
                direction: LedgerDirection::DEBIT,
                status: LedgerStatus::PENDING, // Środki PENDING do debetu
                operationType: 'outgoing_transfer_pending',
                title: $title,
                counterpartyAccount: $toAccountHrb,
                referenceId: (string) $transfer->id // Powiąż z ID przelewu
            );

            // TODO: Potrzebna jest tu dodatkowa logika, aby zbilansować księgę.
            // Obecnie dla przelewu wychodzącego "do banku zewnętrznego" musimy mieć "konto rozliczeniowe"
            // banku, aby zaksięgować kredyt na to konto. Na razie pomijamy ten aspekt dla uproszczenia
            // i skupiamy się na rezerwacji środków na koncie klienta.
            // W fazie clearingu (Moduł: Clearing) to konto rozliczeniowe zostanie wykorzystane.

            return $transfer;
        });
    }

    /**
     * Rejestruje przelew przychodzący do Nexa Bank.
     * Jest to zazwyczaj wywoływane przez system po odebraniu środków z zewnątrz (np. po clearingu).
     */
    public function registerIncomingTransfer(
        string $fromAccountHrb,
        Account $toAccount,
        Money $amount,
        string $title,
        string $referenceId = null // Referencja z systemu zewnętrznego
    ): Transfer {
        if (!$toAccount->isActive()) {
            throw new \RuntimeException('Konto odbiorcy nie jest aktywne.');
        }

        return DB::transaction(function () use ($fromAccountHrb, $toAccount, $amount, $title, $referenceId) {
            // 1. Utwórz rekord przelewu
            $transfer = Transfer::create([
                'from_account_id' => null, // Konto źródłowe jest zewnętrzne
                'to_account_id' => $toAccount->id,
                'from_account_hrb' => $fromAccountHrb,
                'to_account_hrb' => $toAccount->hrb,
                'amount' => $amount->getAmount(),
                'title' => $title,
                'status' => TransferStatus::BOOKED, // Zaksięgowany po odebraniu i przetworzeniu
                'type' => TransferType::INCOMING,
            ]);

            // 2. Zaksięguj przelew w LedgerService
            // Na razie do Ledgera trafia tylko wpis Credit dla konta docelowego
            // W pełnej implementacji przelew przychodzący również musiałby być zbilansowany
            // wpisem debetowym na "koncie rozliczeniowym" banku.
            $this->ledgerService->createEntry(
                account: $toAccount,
                amount: $amount,
                direction: LedgerDirection::CREDIT,
                status: LedgerStatus::BOOKED,
                operationType: 'incoming_transfer',
                title: $title,
                counterpartyAccount: $fromAccountHrb,
                referenceId: $referenceId ?? (string) $transfer->id
            );

            return $transfer;
        });
    }
}