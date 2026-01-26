<div>
    @if ($errorMessage)
        <div class="bg-alert-error-light-bg border-alert-error-light-border text-alert-error-light-text dark:bg-alert-error-dark-bg dark:border-alert-error-dark-border dark:text-alert-error-dark-text px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @endif

    @if ($successMessage)
        <div class="bg-alert-success-light-bg border-alert-success-light-border text-alert-success-light-text dark:bg-alert-success-dark-bg dark:border-alert-success-dark-border dark:text-alert-success-dark-text px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Sukces!</strong>
            <span class="block sm:inline">{{ $successMessage }}</span>
        </div>
    @endif

    <h2 class="text-2xl font-bold mb-4 text-text-primary">Onboarding - Krok {{ $currentStep }} z 6</h2>

    @if ($currentStep === 1)
        <!-- Krok 1: Dane klienta + wybór produktu -->
        <form wire:submit.prevent="firstStepSubmit">
            <div class="mb-4">
                <label for="name" class="block text-text-primary text-sm font-bold mb-2">Imię i Nazwisko:</label>
                <input type="text" wire:model.defer="name" id="name" placeholder="Twoje imię i nazwisko"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 leading-tight focus:outline-none focus:shadow-outline">
                @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="email" class="block text-text-primary text-sm font-bold mb-2">Adres E-mail:</label>
                <input type="email" wire:model.defer="email" id="email" placeholder="Twoj adres e-mail"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 leading-tight focus:outline-none focus:shadow-outline">
                @error('email') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="password" class="block text-text-primary text-sm font-bold mb-2">Hasło:</label>
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 leading-tight focus:outline-none focus:shadow-outline">
                @error('password') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="block text-text-primary text-sm font-bold mb-2">Powtórz Hasło:</label>
                <input type="password" wire:model.defer="password_confirmation" id="password_confirmation" placeholder="Powtórz hasło"
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 leading-tight focus:outline-none focus:shadow-outline">
                @error('password_confirmation') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label for="selectedProductCode" class="block text-text-primary text-sm font-bold mb-2">Produkt Konta:</label>
                @if ($selectedProduct)
                    <div class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-background border-text-secondary">
                        <p><strong>{{ $selectedProduct->name }}</strong></p>
                        <p class="text-sm">{{ $selectedProduct->description }}</p>
                    </div>
                @else
                    <select wire:model="selectedProductCode" id="selectedProductCode"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-text-primary bg-surface border-text-secondary leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">-- Wybierz produkt --</option>
                        @foreach ($accountProducts as $product)
                            <option value="{{ $product->code }}" wire:key="product-{{ $product->code }}">{{ $product->name }} ({{ $product->description }})</option>
                        @endforeach
                    </select>
                @endif
                @error('selectedProductCode') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <x-button type="submit" variant="primary">Dalej</x-button>
        </form>
    @elseif ($currentStep === 2)
        <!-- Krok 2: Przekierowanie do Hubsys WDO dla weryfikacji tożsamości -->
        <div
            x-data="{}"
            x-init="
                Livewire.on('redirect-to-oauth', (url) => {
                    window.location.href = url.url;
                })
            "
            class="text-center text-text-primary"
        >
            <p class="mb-4">Aby zweryfikować Twoją tożsamość, zostaniesz przekierowany do Hubsys WDO.</p>
            <p class="mb-4">Prosimy o potwierdzenie Twojej tożsamości profilem w bankowości.</p>
            <x-button wire:click="redirectToHubsysOauth" variant="primary">Przejdź do Hubsys WDO</x-button>
        </div>
    @elseif ($currentStep === 3)
        <div class="text-text-primary">
            <h3 class="text-xl font-semibold mb-4">Generowanie Umowy</h3>
            <p class="mb-4">Twoja tożsamość została pomyślnie zweryfikowana. Teraz wygenerujemy umowę o otwarcie rachunku bankowego.</p>
            <x-button wire:click="generateAgreement" variant="primary">Generuj Umowę</x-button>

            @if ($agreementContent)
                    <div class="prose max-w-none text-text-primary">
                        {!! $agreementContent !!}
                    </div>
                </div>
                <p class="mt-4 text-sm text-text-secondary">Prosimy o dokładne zapoznanie się z treścią umowy.</p>
                <x-button wire:click="$set('currentStep', 4)" variant="primary" class="mt-4">Przejdź do Podpisu</x-button>
            @endif
        </div>
    @elseif ($currentStep === 4)
        <!-- Krok 4: Podpisywanie umowy Profilem WDO -->
        <div class="text-text-primary">
            <h3 class="text-xl font-semibold mb-4">Podpisanie Umowy</h3>
            <p class="mb-4">Prosimy o podpisanie wygenerowanej umowy Profilem WDO.</p>
            <p class="mb-4 text-xs text-text-secondary">
                W prawdziwym systemie nastąpiłaby tutaj integracja z zewnętrznym dostawcą podpisu kwalifikowanego.
                Dla celów demonstracyjnych używamy symulowanego tokenu.
            </p>
            <x-button wire:click="signAgreement" variant="primary">Podpisz Umowę Profilem WDO</x-button>

            @if ($agreementContent)
                <div class="mt-6 p-4 border rounded-md bg-surface border-text-secondary max-h-96 overflow-y-scroll">
                    <h4 class="text-lg font-semibold mb-2 text-text-primary">Podpisywana Treść Umowy:</h4>
                    <div class="prose max-w-none text-text-primary">
                        {!! $agreementContent !!}
                    </div>
                </div>
            @endif
        </div>

    @elseif ($currentStep === 5)
        <div class="text-text-primary">
            <h3 class="text-xl font-semibold mb-4">Finalizacja Onboardingu</h3>
            <p class="mb-4">Umowa została pomyślnie podpisana. Teraz zakończymy proces i aktywujemy Twoje konto.</p>
            <x-button wire:click="finalizeOnboarding" variant="primary">Zakończ Onboarding</x-button>
        </div>
    @elseif ($currentStep === 6)
        <!-- Krok 6: Onboarding zakończony sukcesem -->
        <div class="text-text-primary">
            <div class="bg-alert-success-light-bg border-alert-success-light-border text-alert-success-light-text dark:bg-alert-success-dark-bg dark:border-alert-success-dark-border dark:text-alert-success-dark-text px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Gratulacje!</strong>
                <span class="block sm:inline">Twój onboarding został pomyślnie zakończony.</span>
            </div>

            @if ($loginCredentials)
                <h3 class="text-xl font-semibold mb-4">Twoje Dane do Logowania:</h3>
                <p class="mb-2"><strong>ID Klienta:</strong> <span class="font-medium">{{ $loginCredentials['customer_id'] }}</span></p>
                <p class="mb-4"><strong>Hasło:</strong> <span class="font-medium">{{ $loginCredentials['password_hint'] }}</span></p>
                <p class="text-sm text-text-secondary">Zalecamy zmianę hasła po pierwszym zalogowaniu.</p>
            @endif

            <x-button href="{{ route('dashboard') }}" variant="primary" class="mt-6">Przejdź do Dashboardu</x-button>
        </div>
    @endif
</div>
