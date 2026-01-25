<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use Modules\Accounts\Models\AccountProduct;
use Modules\Accounts\Services\AccountService;
use App\Models\User;
use Modules\Core\Services\HubsysVerificationService;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateAccount extends Component
{
    public $accountProducts;
    public $selectedProductCode;
    public $accountCreated = false;
    public $errorMessage;
    public $verificationData = ['document_number' => '12345'];

    // Dane użytkownika
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    public $currentStep = 1; // 1: Dane klienta + wybór produktu, 2: Weryfikacja tożsamości, 3: Podsumowanie



    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'selectedProductCode' => 'required',
        'verificationData.document_number' => 'required|string|min:5',
    ];

    public function mount()
    {
        $this->accountProducts = AccountProduct::all();
        $this->selectedProductCode = $this->accountProducts->first()->code ?? null;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function firstStepSubmit(CreatesNewUsers $creator)
    {
        $this->validate([
            'name' => $this->rules['name'],
            'email' => $this->rules['email'],
            'password' => $this->rules['password'],
            'selectedProductCode' => $this->rules['selectedProductCode'],
        ]);

        $user = $creator->create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ]);
        
        session(['onboarding_user_id' => $user->id]);

        $this->currentStep = 2;
    }

    public function secondStepSubmit(HubsysVerificationService $hubsysVerificationService)
    {
        $this->validate([
            'verificationData.document_number' => $this->rules['verificationData.document_number'],
        ]);

        $userId = session('onboarding_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->errorMessage = 'Błąd: Użytkownik nie został znaleziony w sesji.';
            return;
        }

        try {
            if (!$hubsysVerificationService->verifyIdentity($user->customer_id, $this->verificationData)) {
                throw new \Exception('Weryfikacja tożsamości Hubsys nie powiodła się.');
            }
            $this->currentStep = 3;
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas weryfikacji tożsamości: ' . $e->getMessage();
            \Log::error('Błąd weryfikacji tożsamości: ' . $e->getMessage());
        }
    }

    public function thirdStepSubmit(AccountService $accountService) // AccountService jest teraz wstrzyknięty do metody
    {
        $userId = session('onboarding_user_id');
        $user = User::find($userId);

        if (!$user) {
            $this->errorMessage = 'Błąd: Użytkownik nie został znaleziony w sesji.';
            return;
        }

        try {
            $product = AccountProduct::where('code', $this->selectedProductCode)->firstOrFail();
            $accountService->createAccount($user, $product); // Używamy parametru metody
            $this->accountCreated = true;
            session()->forget('onboarding_user_id');
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas tworzenia konta: ' . $e->getMessage();
            \Log::error('Błąd tworzenia konta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.onboarding.create-account');
    }
}