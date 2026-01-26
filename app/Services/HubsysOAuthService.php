<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class HubsysOAuthService
{
    protected $clientId;
    protected $clientSecret;
    protected $redirectUri;
    protected $authorizationUrl;
    protected $tokenUrl;
    protected $userDataUrl;

    public function __construct()
    {
        $this->clientId = config('services.hubsys.client_id');
        $this->clientSecret = config('services.hubsys.client_secret');
        $this->redirectUri = config('services.hubsys.redirect_uri');
        $this->authorizationUrl = config('services.hubsys.authorization_url');
        $this->tokenUrl = config('services.hubsys.token_url');
        $this->userDataUrl = config('services.hubsys.user_data_url');
    }

    /**
     * Generuje URL do przekierowania użytkownika do Hubsys WDO dla OAuth2.1.
     * Zapisuje stan OAuth w sesji dla weryfikacji.
     */
    public function generateAuthorizationUrl(User $user): string
    {
        $state = Str::random(40);
        session(['oauth_state' => $state]); // Zapisz stan w sesji

        // Zapisz stan również w bazie danych użytkownika, aby powiązać go z procesem onboardingu
        $user->wdo_oauth_state = $state;
        $user->save();

        return $this->authorizationUrl . '?' . http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'code',
            'scope' => 'wdo hid', // Zakresy dostępu
            'state' => $state,
        ]);
    }

    /**
     * Obsługuje callback z Hubsys WDO, weryfikuje stan i pobiera token dostępu.
     * Zwraca token dostępu lub rzuca wyjątek w przypadku błędu.
     */
    public function handleCallback(string $code, string $state): string
    {
        if (session('oauth_state') !== $state) {
            throw new \Exception('Invalid OAuth state.');
        }

        session()->forget('oauth_state'); // Usuń stan z sesji po weryfikacji

        $response = Http::asForm()->post($this->tokenUrl, [
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUri,
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new \Exception('Failed to retrieve access token: ' . $response->body());
        }

        return $response->json('access_token');
    }

    /**
     * Pobiera dane użytkownika (WDO, HID, inne) z Hubsys WDO za pomocą tokena dostępu.
     * Zwraca tablicę danych użytkownika.
     */
    public function getUserData(string $accessToken): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ])->get($this->userDataUrl);

        if ($response->failed()) {
            throw new \Exception('Failed to retrieve user data: ' . $response->body());
        }

        return $response->json();
    }
}
