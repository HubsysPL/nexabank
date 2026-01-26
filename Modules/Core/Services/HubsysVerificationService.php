<?php

namespace Modules\Core\Services;

use App\Models\User;

class HubsysVerificationService
{
    /**
     * Weryfikuje dane WDO (Wirtualnego Dowodu Osobistego) i HID.
     * Na tym etapie jest to uproszczona symulacja.
     *
     * @param User $user Użytkownik, dla którego przeprowadzana jest weryfikacja.
     * @param array $wdoData Dane WDO/HID otrzymane z Hubsys OAuth.
     * @return bool True, jeśli weryfikacja zakończyła się sukcesem, w przeciwnym razie false.
     * @throws \Exception W przypadku niepomyślnej weryfikacji.
     */
    public function verifyWdoAndHid(User $user, array $wdoData): bool
    {
        // Symulacja weryfikacji WDO i HID
        // W prawdziwej implementacji tutaj byłaby bardziej złożona logika
        // z uwzględnieniem statusu WDO (aktywne, ważne) i numeru HID.

        if (empty($wdoData['document_number']) || $wdoData['document_number'] !== '12345678') {
            throw new \Exception('Numer WDO jest niepoprawny lub nieaktywny.');
        }

        if (empty($wdoData['hid']) || !is_string($wdoData['hid'])) {
             throw new \Exception('Numer HID jest niepoprawny.');
        }

        // Przykład walidacji: WDO i HID są "aktywne" i "ważne"
        $isWdoValid = $wdoData['document_number'] === '12345678'; // Uproszczona walidacja
        $isHidValid = !empty($wdoData['hid']) && is_string($wdoData['hid']); // Prosta walidacja, że HID istnieje

        if (!$isWdoValid) {
            throw new \Exception('Weryfikacja WDO nie powiodła się: Dokument nieaktywny lub nieważny.');
        }

        if (!$isHidValid) {
             throw new \Exception('Weryfikacja HID nie powiodła się: Numer HID nieaktywny lub nieważny.');
        }

        // Po pomyślnej weryfikacji, zaktualizuj dane użytkownika
        $user->document_number = $wdoData['document_number'];
        $user->document_type = $wdoData['document_type'] ?? 'WDO'; // Domyślnie WDO
        // Dalsze pola WDO: issue_date, expiry_date, issued_by (jeśli dostępne w $wdoData)
        $user->hid = $wdoData['hid'];
        $user->wdo_verified_at = now();
        $user->save();

        return true;
    }
}
