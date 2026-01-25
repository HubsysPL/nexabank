<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Ledger\Models\LedgerEntry;
use Modules\Accounts\Models\Account;
use App\Models\User; // Assuming current logged in user

class LedgerTable extends Component
{
    use WithPagination;

    public $account;
    public $errorMessage;

    protected $queryString = ['page'];

    public function mount()
    {
        try {
            // TODO: Replace with actual logged in user and their primary account
            $user = User::with('accounts')->first();
            if (!$user || $user->accounts->isEmpty()) {
                throw new \Exception('Brak konta dla zalogowanego użytkownika.');
            }

            $this->account = $user->accounts->first();
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas ładowania danych konta: ' . $e->getMessage();
            \Log::error('Błąd ładowania danych konta dla LedgerTable: ' . $e->getMessage());
        }
    }

    public function getLedgerEntriesProperty()
    {
        if (!$this->account) {
            return collect();
        }

        return LedgerEntry::where('account_id', $this->account->id)
            ->latest('booked_at')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.dashboard.ledger-table', [
            'ledgerEntries' => $this->ledgerEntries,
        ]);
    }
}
