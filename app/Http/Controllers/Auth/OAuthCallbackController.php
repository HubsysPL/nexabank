<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\OnboardingService;
use Illuminate\Support\Facades\Log;

class OAuthCallbackController extends Controller
{
    protected OnboardingService $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    public function handleHubsysCallback(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'state' => 'required|string',
        ]);

        try {
            // Znajdź użytkownika po stanie OAuth
            $user = User::where('wdo_oauth_state', $request->state)->firstOrFail();

            // Przetwórz callback OAuth i weryfikuj dane użytkownika
            $updatedUser = $this->onboardingService->handleHubsysOAuthCallback(
                $user,
                $request->code,
                $request->state
            );

            // Po pomyślnej weryfikacji, przekieruj z powrotem do onboardingu
            // i ustaw session('onboarding_user_id') aby Livewire mógł kontynuować
            session(['onboarding_user_id' => $updatedUser->id]);
            session()->flash('success', 'Weryfikacja tożsamości Hubsys WDO zakończona pomyślnie!');

            return redirect()->route('onboarding');

        } catch (\Exception $e) {
            Log::error('Hubsys OAuth Callback Error: ' . $e->getMessage());
            session()->flash('error', 'Błąd podczas weryfikacji tożsamości: ' . $e->getMessage());

            // Przekieruj z powrotem do onboardingu z błędem
            return redirect()->route('onboarding');
        }
    }
}
