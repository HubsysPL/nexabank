<?php

use App\Models\User;
use App\Models\OnboardingAgreement;
use App\Services\OnboardingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Modules\Accounts\Models\AccountProduct;
// use Tests\TestCase; // Można usunąć, jeśli Pest automatycznie to obsługuje

// uses(Tests\TestCase::class, RefreshDatabase::class); // To jest obsługiwane przez tests/Pest.php dla katalogu Feature

beforeEach(function () {
    // Tworzymy przykładowy produkt konta dla testów
    AccountProduct::create([
        'name' => 'Konto Standard',
        'code' => 'STANDARD_ACCOUNT',
        'description' => 'Standardowe konto osobiste',
        'interest_rate' => 0.01,
        'monthly_fee' => 0,
    ]);

    // Ustawienie zmiennych środowiskowych dla Hubsys (symulacja)
    config()->set('services.hubsys.client_id', 'test_client_id');
    config()->set('services.hubsys.client_secret', 'test_client_secret');
    config()->set('services.hubsys.redirect_uri', 'http://localhost/oauth/callback/hubsys');
    config()->set('services.hubsys.authorization_url', 'http://hubsys.test/oauth/authorize');
    config()->set('services.hubsys.token_url', 'http://hubsys.test/oauth/token');
    config()->set('services.hubsys.user_data_url', 'http://hubsys.test/api/user');
});

it('a user can complete the full onboarding process', function () {
    // Krok 1: Inicjacja onboardingu - dane klienta i wybór produktu
    Livewire::test('onboarding.create-account')
        ->set('name', 'Jan Kowalski')
        ->set('email', 'jan.kowalski@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('selectedProductCode', 'STANDARD_ACCOUNT')
        ->call('firstStepSubmit')
        ->assertSet('currentStep', 2)
        ->assertEmitted('redirect-to-oauth');

    $user = User::where('email', 'jan.kowalski@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->onboarding_status)->toBe('user_created');
    expect(Session::get('onboarding_user_id'))->not->toBeNull();
    expect($user->wdo_oauth_state)->not->toBeNull();

    // Symulacja przekierowania do Hubsys OAuth i powrotu (bez faktycznego przekierowania)
    // Musimy zasymulować odpowiedź z serwera Hubsys
    Http::fake([
        'hubsys.test/oauth/token' => Http::response([
            'access_token' => 'mock_access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
        'hubsys.test/api/user' => Http::response([
            'document_number' => '12345678',
            'hid' => 'mock_hid_123',
            // Inne dane użytkownika z Hubsys
        ], 200),
    ]);

    // Wywołanie callbacku OAuth
    $response = $this->get('/oauth/callback/hubsys?code=mock_code&state=' . $user->wdo_oauth_state);
    $response->assertRedirect('/onboarding');
    $response->assertSessionHas('success', 'Weryfikacja tożsamości Hubsys WDO zakończona pomyślnie!');

    $user->refresh(); // Odśwież użytkownika po callbacku
    expect($user->onboarding_status)->toBe('wdo_verified');
    expect($user->document_number)->toBe('12345678');
    expect($user->hid)->toBe('mock_hid_123');
    expect($user->wdo_verified_at)->not->toBeNull();
    expect($user->wdo_oauth_state)->toBeNull(); // Stan powinien być wyczyszczony

    // Krok 3: Generowanie umowy
    Livewire::test('onboarding.create-account', ['onboardingUser' => $user])
        ->set('onboardingStatus', 'wdo_verified')
        ->set('currentStep', 3) // Ustawiamy stan ręcznie, jakby komponent został odświeżony po przekierowaniu
        ->call('generateAgreement')
        ->assertSet('currentStep', 4)
        ->assertSet('onboardingStatus', 'agreement_generated')
        ->assertSee('Umowa została pomyślnie wygenerowana.'); // Sprawdź, czy wiadomość sukcesu się pojawia

    $user->refresh();
    expect($user->onboarding_status)->toBe('agreement_generated');
    expect($user->agreement_id)->not->toBeNull();
    $this->assertDatabaseHas('onboarding_agreements', [
        'user_id' => $user->id,
        'status' => 'generated',
    ]);

    // Krok 4: Podpisywanie umowy Profilem WDO
    $agreement = OnboardingAgreement::where('user_id', $user->id)->first();
    expect($agreement)->not->toBeNull();

    Livewire::test('onboarding.create-account', ['onboardingUser' => $user])
        ->set('onboardingStatus', 'agreement_generated')
        ->set('currentStep', 4)
        ->call('signAgreement')
        ->assertSet('currentStep', 5)
        ->assertSet('onboardingStatus', 'agreement_signed')
        ->assertSee('Umowa została pomyślnie podpisana.');

    $user->refresh();
    expect($user->onboarding_status)->toBe('agreement_signed');
    expect($user->agreement_signed_at)->not->toBeNull();
    $this->assertDatabaseHas('onboarding_agreements', [
        'id' => $agreement->id,
        'user_id' => $user->id,
        'status' => 'signed',
        'signed_by' => 'WDO_Profile',
    ]);

    // Krok 5: Finalizacja onboardingu
    Livewire::test('onboarding.create-account', ['onboardingUser' => $user])
        ->set('onboardingStatus', 'agreement_signed')
        ->set('currentStep', 5)
        ->set('selectedProductCode', 'STANDARD_ACCOUNT') // Konieczne do tworzenia konta
        ->call('finalizeOnboarding')
        ->assertSet('currentStep', 6)
        ->assertSet('onboardingStatus', 'completed')
        ->assertSet('accountCreated', true)
        ->assertSee('Onboarding zakończony sukcesem!');
    
    $user->refresh();
    expect($user->onboarding_status)->toBe('completed');
    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'status' => 'active',
    ]);
    expect(Session::get('onboarding_user_id'))->toBeNull(); // Sesja powinna być wyczyszczona
});

it('an oauth callback with invalid state throws exception', function () {
    $user = User::factory()->create(['wdo_oauth_state' => 'valid_state']);

    $this->get('/oauth/callback/hubsys?code=mock_code&state=invalid_state')
        ->assertSessionHas('error', 'Błąd podczas weryfikacji tożsamości: Invalid OAuth state.')
        ->assertRedirect('/onboarding');
});

it('an oauth callback with failed token retrieval throws exception', function () {
    $user = User::factory()->create(['wdo_oauth_state' => 'valid_state']);
    Session::put('oauth_state', 'valid_state'); // Ustawiamy stan w sesji

    Http::fake([
        'hubsys.test/oauth/token' => Http::response([], 500), // Symulujemy błąd serwera
    ]);

    $this->get('/oauth/callback/hubsys?code=mock_code&state=valid_state')
        ->assertSessionHas('error', 'Błąd podczas weryfikacji tożsamości: Failed to retrieve access token: ')
        ->assertRedirect('/onboarding');
});

it('an oauth callback with failed user data retrieval throws exception', function () {
    $user = User::factory()->create(['wdo_oauth_state' => 'valid_state']);
    Session::put('oauth_state', 'valid_state'); // Ustawiamy stan w sesji

    Http::fake([
        'hubsys.test/oauth/token' => Http::response([
            'access_token' => 'mock_access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 200),
        'hubsys.test/api/user' => Http::response([], 500), // Symulujemy błąd serwera
    ]);

    $this->get('/oauth/callback/hubsys?code=mock_code&state=valid_state')
        ->assertSessionHas('error', 'Błąd podczas weryfikacji tożsamości: Failed to retrieve user data: ')
        ->assertRedirect('/onboarding');
});