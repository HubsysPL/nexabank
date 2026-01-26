<?php

namespace App\Livewire\Onboarding;

use Livewire\Component;
use App\Models\User;
use App\Models\OnboardingAgreement;
use App\Services\OnboardingService;
use Modules\Accounts\Models\AccountProduct;

class CreateAccount extends Component
{
    protected OnboardingService $onboardingService;

    public $accountProducts;
    public $selectedProductCode;
    public ?AccountProduct $selectedProduct = null;
    public ?string $promotionCode = null;
    public $accountCreated = false;
    public $errorMessage;
    public $successMessage;

    // Dane użytkownika
    public $name;
    public $email;
    public $password;
    public $password_confirmation;

    // Nowe właściwości do obsługi onboardingu
    public $currentStep = 1; // 1: Dane klienta + wybór produktu, 2: OAuth Redirect, 3: WDO/HID Verified, 4: Agreement, 5: Signed, 6: Completed
    public $onboardingStatus; // Status z user->onboarding_status
    public $agreementContent; // Treść wygenerowanej umowy
    public $wdoSignatureToken = 'SIMULATED_WDO_SIGNATURE'; // Token do symulacji podpisu WDO
    public $loginCredentials = null; // Dane do logowania po zakończeniu onboardingu
    public ?User $onboardingUser = null; // Użytkownik w trakcie onboardingu

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'selectedProductCode' => 'required',
    ];

    public function boot(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    public function mount()
    {
        $this->promotionCode = request()->query('promo_code');

        if ($this->promotionCode) {
            $this->selectedProduct = AccountProduct::where('code', $this->promotionCode)->first();
            if($this->selectedProduct) {
                $this->selectedProductCode = $this->selectedProduct->code;
            }
        }

        if (!$this->selectedProduct) {
            $this->accountProducts = AccountProduct::all();
            if ($this->accountProducts->isNotEmpty()) {
                $this->selectedProductCode = $this->accountProducts->first()->code;
            }
        }


        if (session('onboarding_user_id')) {
            $this->onboardingUser = User::find(session('onboarding_user_id'));
            if ($this->onboardingUser) {
                $this->onboardingStatus = $this->onboardingUser->onboarding_status;
                $this->syncStateFromUser();
            } else {
                session()->forget('onboarding_user_id');
            }
        }
        $this->updateCurrentStepFromStatus();
    }

    private function syncStateFromUser()
    {
        if ($this->onboardingUser) {
            $this->name = $this->onboardingUser->name;
            $this->email = $this->onboardingUser->email;
        }
    }

    private function updateCurrentStepFromStatus()
    {
        switch ($this->onboardingStatus) {
            case 'initial':
                $this->currentStep = 1;
                break;
            case 'user_created':
                $this->currentStep = 2; // OAuth Redirect
                break;
            case 'oauth_redirected': 
            case 'wdo_verified':
                $this->currentStep = 3; // Generowanie umowy
                if ($this->onboardingUser && $this->onboardingUser->agreement_id) {
                    $agreement = OnboardingAgreement::find($this->onboardingUser->agreement_id);
                    if ($agreement) {
                        $this->agreementContent = $agreement->content;
                    }
                }
                break;
            case 'agreement_generated':
                $this->currentStep = 4; // Podpisywanie umowy
                 if ($this->onboardingUser && $this->onboardingUser->agreement_id) {
                    $agreement = OnboardingAgreement::find($this->onboardingUser->agreement_id);
                    if ($agreement) {
                        $this->agreementContent = $agreement->content;
                    }
                }
                break;
            case 'agreement_signed':
                $this->currentStep = 5; // Finalizacja / Dane do logowania
                break;
            case 'completed':
                $this->currentStep = 6; // Onboarding zakończony
                $this->accountCreated = true;
                break;
            default:
                $this->currentStep = 1;
                break;
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function firstStepSubmit()
    {
        try {
            $this->validate([
                'name' => $this->rules['name'],
                'email' => $this->rules['email'],
                'password' => $this->rules['password'],
                'selectedProductCode' => $this->rules['selectedProductCode'],
            ]);

            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'password_confirmation' => $this->password_confirmation,
            ];

            $this->onboardingUser = $this->onboardingService->initiateOnboarding($userData, $this->selectedProductCode);
            session(['onboarding_user_id' => $this->onboardingUser->id]);
            $this->onboardingStatus = $this->onboardingUser->onboarding_status;
            $this->updateCurrentStepFromStatus();
            $this->successMessage = 'Krok 1 zakończony pomyślnie. Przechodzimy do weryfikacji tożsamości.';

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorMessage = $e->getMessage();
            throw $e; 
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas inicjowania onboardingu: ' . $e->getMessage();
            \Log::error('Błąd inicjowania onboardingu: ' . $e->getMessage());
        }
    }

    /**
     * Przekierowuje użytkownika do Hubsys WDO dla autoryzacji OAuth2.1.
     */
    public function redirectToHubsysOauth()
    {
        if (!$this->onboardingUser || $this->onboardingUser->onboarding_status !== 'user_created') {
            $this->errorMessage = 'Błąd: Użytkownik nie jest w odpowiednim stanie do weryfikacji WDO.';
            return;
        }

        try {
            $authorizationUrl = $this->onboardingService->getHubsysAuthorizationUrl($this->onboardingUser);
            $this->onboardingUser->onboarding_status = 'oauth_redirected';
            $this->onboardingUser->save();

            $this->dispatch('redirect-to-oauth', url: $authorizationUrl);
            
        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas generowania URL autoryzacji Hubsys: ' . $e->getMessage();
            \Log::error('Błąd generowania URL autoryzacji Hubsys: ' . $e->getMessage());
        }
    }

    /**
     * Generuje umowę o otwarcie rachunku bankowego.
     */
    public function generateAgreement()
    {
        if (!$this->onboardingUser || ($this->onboardingUser->onboarding_status !== 'wdo_verified' && $this->onboardingUser->onboarding_status !== 'oauth_redirected')) {
            $this->errorMessage = 'Błąd: Użytkownik nie jest w odpowiednim stanie do generowania umowy.';
            return;
        }

        try {
            $agreement = $this->onboardingService->generateBankAccountAgreement($this->onboardingUser, $this->selectedProductCode);
            $this->agreementContent = $agreement->content;
            $this->onboardingStatus = $this->onboardingUser->onboarding_status; 
            $this->updateCurrentStepFromStatus();
            $this->successMessage = 'Umowa została pomyślnie wygenerowana. Przejdź do podpisu.';

        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas generowania umowy: ' . $e->getMessage();
            \Log::error('Błąd generowania umowy: ' . $e->getMessage());
        }
    }

    /**
     * Podpisuje umowę Profilem WDO.
     */
    public function signAgreement()
    {
        if (!$this->onboardingUser || $this->onboardingUser->onboarding_status !== 'agreement_generated') {
            $this->errorMessage = 'Błąd: Użytkownik nie jest w odpowiednim stanie do podpisywania umowy.';
            return;
        }

        try {
            $signatureData = ['wdo_signature_token' => $this->wdoSignatureToken]; 

            $agreement = OnboardingAgreement::where('user_id', $this->onboardingUser->id)
                                            ->where('status', 'generated')
                                            ->firstOrFail();

            $signedAgreement = $this->onboardingService->signAgreementWithWdo($agreement, $signatureData);
            $this->onboardingUser = $signedAgreement->user; 
            $this->onboardingStatus = $this->onboardingUser->onboarding_status; 
            $this->updateCurrentStepFromStatus();
            $this->successMessage = 'Umowa została pomyślnie podpisana. Przechodzimy do finalizacji.';

        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas podpisywania umowy: ' . $e->getMessage();
            \Log::error('Błąd podpisywania umowy: ' . $e->getMessage());
        }
    }

    /**
     * Finalizuje proces onboardingu, tworząc konto bankowe i generując dane do logowania.
     */
    public function finalizeOnboarding()
    {
        if (!$this->onboardingUser || $this->onboardingUser->onboarding_status !== 'agreement_signed') {
            $this->errorMessage = 'Błąd: Użytkownik nie jest w odpowiednim stanie do finalizacji onboardingu.';
            return;
        }

        try {
            $this->onboardingUser = $this->onboardingService->finalizeOnboarding($this->onboardingUser, $this->selectedProductCode);
            $this->onboardingStatus = $this->onboardingUser->onboarding_status; 
            $this->updateCurrentStepFromStatus();
            $this->accountCreated = true;
            session()->forget('onboarding_user_id');
            $this->successMessage = 'Onboarding zakończony sukcesem! Poniżej Twoje dane do logowania.';

            $this->loginCredentials = [
                'customer_id' => $this->onboardingUser->customer_id,
                'password_hint' => 'Hasło ustawione podczas rejestracji.',
            ];

        } catch (\Exception $e) {
            $this->errorMessage = 'Wystąpił błąd podczas finalizacji onboardingu: ' . $e->getMessage();
            \Log::error('Błąd finalizacji onboardingu: ' . $e->getMessage());
        }
    }


    public function render()
    {
        return view('livewire.onboarding.create-account');
    }
}