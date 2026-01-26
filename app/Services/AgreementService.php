<?php

namespace App\Services;

use App\Models\User;
use App\Models\OnboardingAgreement;
use Illuminate\Support\Facades\View;

class AgreementService
{
    /**
     * Generuje treść umowy na podstawie szablonu i danych użytkownika.
     *
     * @param User $user Użytkownik, dla którego generowana jest umowa.
     * @param array $userData Dodatkowe dane użytkownika do szablonu.
     * @param string $selectedProductCode Kod wybranego produktu konta.
     * @param string $templateName Nazwa szablonu Blade (np. 'agreements.bank_account_opening').
     * @return OnboardingAgreement Nowo utworzony obiekt umowy.
     * @throws \Exception Jeśli szablon nie istnieje lub generowanie się nie powiedzie.
     */
    public function generateAgreement(User $user, array $userData, string $selectedProductCode, string $templateName = 'agreements.bank_account_opening'): OnboardingAgreement
    {
        if (!View::exists($templateName)) {
            throw new \Exception("Szablon umowy '{$templateName}' nie istnieje.");
        }

        // Przygotuj dane dla szablonu umowy
        $data = array_merge($userData, [
            'user' => $user,
            'agreement_date' => now()->format('Y-m-d H:i:s'),
            'hubsys_clause' => 'Niniejsza umowa jest ważna wyłącznie w ekosystemie Hubsys i nie stanowi zobowiązania wobec prawdziwego banku komercyjnego.',
            'selectedProductCode' => $selectedProductCode, // Dodano kod produktu
        ]);

        $agreementContent = View::make($templateName, $data)->render();

        // Zapisz umowę w bazie danych
        $agreement = new OnboardingAgreement([
            'user_id' => $user->id,
            'content' => $agreementContent,
            'status' => 'generated',
        ]);
        $agreement->save();

        return $agreement;
    }

    /**
     * Obsługuje logiczne "podpisanie" umowy Profilem WDO.
     *
     * @param OnboardingAgreement $agreement Obiekt umowy do podpisania.
     * @param string $signatureMethod Metoda podpisu (np. 'WDO_Profile').
     * @param array $signatureData Dodatkowe dane dotyczące podpisu.
     * @return OnboardingAgreement Podpisany obiekt umowy.
     * @throws \Exception Jeśli umowa jest już podpisana lub podpis się nie powiedzie.
     */
    public function signAgreement(OnboardingAgreement $agreement, string $signatureMethod, array $signatureData = []): OnboardingAgreement
    {
        if ($agreement->status === 'signed') {
            throw new \Exception('Umowa jest już podpisana.');
        }

        // Symulacja procesu podpisywania Profilem WDO
        // W rzeczywistości byłaby tu integracja z zewnętrznym serwisem podpisu
        // lub walidacja tokenu podpisu.
        if (empty($signatureData['wdo_signature_token']) || $signatureData['wdo_signature_token'] !== 'SIMULATED_WDO_SIGNATURE') {
            throw new \Exception('Nieprawidłowy token podpisu WDO.');
        }

        $agreement->signed_content = $agreement->content; // Na potrzeby symulacji, podpisana treść to ta sama treść
        $agreement->status = 'signed';
        $agreement->signed_by = $signatureMethod;
        $agreement->signed_at = now();
        $agreement->save();

        // Zaktualizuj użytkownika o ID podpisanej umowy
        $agreement->user->agreement_id = $agreement->id;
        $agreement->user->agreement_signed_at = now();
        $agreement->user->save();

        return $agreement;
    }
}
