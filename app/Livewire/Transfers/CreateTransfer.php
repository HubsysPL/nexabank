<?php

namespace App\Livewire\Transfers;

use Livewire\Component;
use Modules\Accounts\Models\Account;
use Modules\Transfers\Services\TransferService;
use Modules\Core\ValueObjects\Money;
use App\Models\User; // Assuming current logged in user

class CreateTransfer extends Component
{
    public $fromAccount;
    public $toAccountHrb;
    public $amount;
    public $title;
    public $transferSuccess = false;
    public $errorMessage;

    protected TransferService $transferService;

    protected $rules = [
        'fromAccount' => 'required',
        'toAccountHrb' => 'required|string|size:24',
        'amount' => 'required|numeric|min:0.01',
        'title' => 'required|string|max:255',
    ];

    public function mount(TransferService $transferService)
    {
        $this->transferService = $transferService;
        
        // TODO: Replace with actual logged in user and their primary account
        $user = User::with('accounts')->first();
        $this->fromAccount = $user->accounts->first()->id ?? null;
    }

    public function createTransfer()
    {
        $this->resetErrorBag();
        $this->transferSuccess = false;
        $this->errorMessage = null;

        $this->validate();

        try {
            $sourceAccount = Account::find($this->fromAccount);

            if (!$sourceAccount) {
                throw new \Exception('Konto źródłowe nie zostało znalezione.');
            }

            $moneyAmount = Money::fromFloat((float) $this->amount);

            // Determine if it's an internal or external transfer
            $toInternalAccount = Account::where('hrb', $this->toAccountHrb)->first();

            if ($toInternalAccount) {
                // Internal transfer
                $this->transferService->initiateInternalTransfer(
                    fromAccount: $sourceAccount,
                    toAccount: $toInternalAccount,
                    amount: $moneyAmount,
                    title: $this->title
                );
            } else {
                // Outgoing transfer
                $this->transferService->initiateOutgoingTransfer(
                    fromAccount: $sourceAccount,
                    toAccountHrb: $this->toAccountHrb,
                    amount: $moneyAmount,
                    title: $this->title
                );
            }

            $this->transferSuccess = true;
            $this->resetForm();
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas zlecania przelewu: ' . $e->getMessage();
            \Log::error('Błąd zlecania przelewu: ' . $e->getMessage());
        }
    }

    protected function resetForm()
    {
        $this->toAccountHrb = '';
        $this->amount = '';
        $this->title = '';
    }

    public function render()
    {
        // TODO: Pass actual user's accounts
        $userAccounts = User::with('accounts')->first()->accounts ?? collect();

        return view('livewire.transfers.create-transfer', [
            'userAccounts' => $userAccounts,
        ]);
    }
}
