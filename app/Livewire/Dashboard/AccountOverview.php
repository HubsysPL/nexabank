<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Modules\Accounts\Models\Account;
use Modules\Ledger\Services\LedgerService;
use App\Models\User; // Assuming current logged in user

class AccountOverview extends Component
{
    public $account;
    public $balance;
    public $errorMessage;

    protected LedgerService $ledgerService;

    public function mount(LedgerService $ledgerService)
    {
        $this->ledgerService = $ledgerService;
        
        try {
            // TODO: Replace with actual logged in user and their primary account
            $user = User::with('accounts')->first(); 
            if (!$user || $user->accounts->isEmpty()) {
                throw new \Exception('Brak konta dla zalogowanego użytkownika.');
            }
            
            $this->account = $user->accounts->first();
            $this->balance = $this->ledgerService->calculateBalance($this->account)->format(); // Display formatted balance
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas ładowania danych konta: ' . $e->getMessage();
            \Log::error('Błąd ładowania danych konta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.dashboard.account-overview');
    }
}
