<?php

namespace Modules\Clearing\Console;

use Illuminate\Console\Command;
use Modules\Clearing\Services\ClearingService;
use Modules\Core\Enums\ClearingBatchStatus;

class ProcessClearingBatch extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'clearing:process-batch';

    /**
     * The console command description.
     */
    protected $description = 'Przetwarza oczekujące przelewy wychodzące w ramach batcha clearingowego.';

    protected ClearingService $clearingService;

    /**
     * Create a new command instance.
     */
    public function __construct(ClearingService $clearingService)
    {
        parent::__construct();
        $this->clearingService = $clearingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Rozpoczynanie przetwarzania batcha clearingowego...');

        try {
            $batch = $this->clearingService->processPendingOutgoingTransfers();

            if ($batch->status === ClearingBatchStatus::COMPLETED) {
                $this->info("Batch clearingowy {$batch->id} zakończony pomyślnie. Przetworzono {$batch->processed_transfers_count} przelewów.");
                return Command::SUCCESS;
            } elseif ($batch->status === ClearingBatchStatus::FAILED) {
                $this->error("Batch clearingowy {$batch->id} zakończony niepowodzeniem.");
                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("Wystąpił nieoczekiwany błąd podczas przetwarzania batcha clearingowego: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
