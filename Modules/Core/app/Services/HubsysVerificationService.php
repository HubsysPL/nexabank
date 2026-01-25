<?php

namespace Modules\Core\Services;

class HubsysVerificationService
{
    /**
     * Symuluje weryfikację tożsamości Hubsys ID (HID) i Wirtualnego Dowodu Osobistego (WDO).
     *
     * @param string $clientId ID klienta (CID)
     * @param array $verificationData Dane do weryfikacji (np. numer dowodu, PESEL itp.)
     * @return bool True jeśli weryfikacja pomyślna, false w przeciwnym razie.
     */
    public function verifyIdentity(string $clientId, array $verificationData): bool
    {
        // Tutaj można dodać logikę mockowania, np. dla testowego klienta zawsze true
        if ($clientId === '12345678') { // Przykładowe testowe ID klienta
            return true;
        }

        // W bardziej rozbudowanym mocku można symulować różne scenariusze
        // np. na podstawie verificationData, losowości, itp.
        return (bool) rand(0, 1); // Losowy wynik weryfikacji
    }
}
