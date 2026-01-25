<?php

namespace Modules\Clearing\Services;

use Illuminate\Support\Facades\DB;
use Modules\Clearing\Models\ClearingBatch;
use Modules\Core\Enums\ClearingBatchStatus;
use Modules\Core\Enums\LedgerStatus;
use Modules\Core\Enums\TransferStatus;
use Modules\Core\Enums\TransferType;
use Modules\Ledger\Models\LedgerEntry;
use Modules\Ledger\Services\LedgerService;
use Modules\Transfers\Models\Transfer;

class ClearingService
{
    protected LedgerService $ledgerService;

    public function __construct(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
    }

    public function processPendingOutgoingTransfers(): ClearingBatch
    {
        $batch = ClearingBatch::create([
            'status' => ClearingBatchStatus::IN_PROGRESS,
            'started_at' => now(),
        ]);

        $processedCount = 0;
        $failed = false;

        try {
            DB::transaction(function () use ($batch, &$processedCount) {
                $pendingTransfers = Transfer::where('type', TransferType::OUTGOING)
                    ->where('status', TransferStatus::PENDING)
                    ->get();

                foreach ($pendingTransfers as $transfer) {
                    // Update Ledger Entries from PENDING to BOOKED
                    LedgerEntry::where('reference_id', $transfer->id)
                        ->where('status', LedgerStatus::PENDING)
                        ->update(['status' => LedgerStatus::BOOKED, 'booked_at' => now()]);

                    // Update Transfer status
                    $transfer->update(['status' => TransferStatus::BOOKED]);

                    // Update cached account balances (assuming there's a mechanism for this,
                    // or re-calculate using ledgerService->calculateBalance and then update account.balance)
                    // For simplicity, we are assuming the `bookInternalTransfer` in `LedgerService` handles `accounts.balance` updates.
                    // For outgoing transfers, the balance decrement happened during initiation,
                    // so no further direct decrement is needed here on `fromAccount`.
                    // However, we need to ensure the `toAccount` part for interbank transfers eventually balances.
                    // This is where the 'technical account' logic would come in.
                    // For now, the `balance` cache on `fromAccount` would already reflect the pending debit.

                    $processedCount++;
                }

                $batch->status = ClearingBatchStatus::COMPLETED;
                $batch->processed_transfers_count = $processedCount;
            });
        } catch (\Throwable $e) {
            $failed = true;
            $batch->status = ClearingBatchStatus::FAILED;
            // Log the error
            \Log::error("Clearing Batch {$batch->id} failed: " . $e->getMessage());
        } finally {
            $batch->completed_at = now();
            $batch->save();
        }

        return $batch;
    }
}
