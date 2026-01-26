<?php

namespace App\Services;

use App\Models\User;
use App\Models\OnboardingAgreement;
use Modules\Accounts\Models\AccountProduct;
use Modules\Accounts\Services\AccountService;
use Modules\Core\Services\HubsysVerificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class OnboardingService
{
    protected HubsysOAuthService $hubsysOAuthService;
    protected HubsysVerificationService $hubsysVerificationService;
    protected AgreementService $agreementService;
    protected AccountService $accountService; // Zakładam, że ten serwis istnieje
    protected CreatesNewUsers $createsNewUsers;

    public function __construct(
        HubsysOAuthService $hubsysOAuthService,
        HubsysVerificationService $hubsysVerificationService,
        AgreementService $agreementService,
        AccountService $accountService,
        CreatesNewUsers $createsNewUsers
    ) {
        $this->hubsysOAuthService = $hubsysOAuthService;
        $this->hubsysVerificationService = $hubsysVerificationService;
        $this->agreementService = $agreementService;
        $this->accountService = $accountService;
        $this->createsNewUsers = $createsNewUsers;
    }

    /**
     * Inicjuje proces onboardingu, tworząc użytkownika i ustawiając początkowy status.
     *
     * @param array $userData Dane do stworzenia użytkownika (name, email, password).
     * @param string $productCode Kod wybranego produktu konta.
     * @return User
     * @throws \Exception
     */
    public function initiateOnboarding(array $userData, string $productCode): User
    {
        return DB::transaction(function () use ($userData, $productCode) {
            // Walidacja danych użytkownika
            // Ta walidacja jest już w CreateNewUsers, ale można tu dodać dodatkowe.
            
            $user = $this->createsNewUsers->create($userData);

            // Sprawdź, czy produkt istnieje
            AccountProduct::where('code', $productCode)->firstOrFail();

            $user->onboarding_status = 'user_created';
            $user->save();

            return $user;
        });
    }

    /**
     * Generuje URL do przekierowania użytkownika do Hubsys WDO dla OAuth2.1.
     *
     * @param User $user
     * @return string URL autoryzacji
     */
    public function getHubsysAuthorizationUrl(User $user): string
    {
        return $this->hubsysOAuthService->generateAuthorizationUrl($user);
    }

    /**
     * Obsługuje callback z Hubsys OAuth, pobiera dane użytkownika i weryfikuje je.
     *
     * @param User $user Użytkownik, dla którego przetwarzany jest callback.
     * @param string $code Kod autoryzacji z callbacka.
     * @param string $state Stan z callbacka do weryfikacji.
     * @return User Zaktualizowany użytkownik.
     * @throws \Exception
     */
    public function handleHubsysOAuthCallback(User $user, string $code, string $state): User
    {
        return DB::transaction(function () use ($user, $code, $state) {
            $accessToken = $this->hubsysOAuthService->handleCallback($code, $state);
            $hubsysUserData = $this->hubsysOAuthService->getUserData($accessToken);

            // Przygotuj dane WDO/HID do weryfikacji
            $wdoData = [
                'document_number' => $hubsysUserData['document_number'] ?? null,
                'hid' => $hubsysUserData['hid'] ?? null,
                // Możesz tu dodać inne dane WDO z Hubsys
            ];

            // Weryfikuj WDO i HID
            $this->hubsysVerificationService->verifyWdoAndHid($user, $wdoData);

            $user->onboarding_status = 'wdo_verified';
            $user->wdo_oauth_state = null; // Wyczyść stan OAuth
            $user->save();

            return $user;
        });
    }

    /**
     * Generuje umowę o otwarcie rachunku bankowego dla użytkownika.
     *
     * @param User $user
     * @param string $selectedProductCode
     * @return OnboardingAgreement Nowo utworzona umowa.
     * @throws \Exception
     */
    public function generateBankAccountAgreement(User $user, string $selectedProductCode): OnboardingAgreement
    {
        return DB::transaction(function () use ($user, $selectedProductCode) {
            $agreement = $this->agreementService->generateAgreement($user, [
                'user_full_name' => $user->name,
                'user_email' => $user->email,
                'user_customer_id' => $user->customer_id,
            ], $selectedProductCode);

            $user->onboarding_status = 'agreement_generated';
            $user->save();

            return $agreement;
        });
    }

    /**
     * Podpisuje umowę Profilem WDO.
     *
     * @param OnboardingAgreement $agreement
     * @param array $signatureData Dane do podpisu (np. token podpisu).
     * @return OnboardingAgreement Podpisana umowa.
     * @throws \Exception
     */
    public function signAgreementWithWdo(OnboardingAgreement $agreement, array $signatureData): OnboardingAgreement
    {
        return DB::transaction(function () use ($agreement, $signatureData) {
            $signedAgreement = $this->agreementService->signAgreement($agreement, 'WDO_Profile', $signatureData);

            $signedAgreement->user->onboarding_status = 'agreement_signed';
            $signedAgreement->user->save();

            return $signedAgreement;
        });
    }

    /**
     * Finalizuje onboarding, tworząc konto bankowe i aktualizując status użytkownika.
     *
     * @param User $user
     * @param string $productCode
     * @return User Zaktualizowany użytkownik.
     * @throws \Exception
     */
    public function finalizeOnboarding(User $user, string $productCode): User
    {
        return DB::transaction(function () use ($user, $productCode) {
            // Sprawdź, czy użytkownik ma już przypisany agreement_id i agreement_signed_at
            if (empty($user->agreement_id) || empty($user->agreement_signed_at)) {
                throw new \Exception('Umowa nie została podpisana.');
            }

            $product = AccountProduct::where('code', $productCode)->firstOrFail();
            $this->accountService->createAccount($user, $product);

            $user->onboarding_status = 'completed';
            $user->save();

            // Tu można wygenerować dane do logowania i wysłać je użytkownikowi
            // lub przekierować do strony z danymi do logowania.

            return $user;
        });
    }
}
