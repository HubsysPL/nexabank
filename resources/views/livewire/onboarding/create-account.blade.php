<div>
    @if ($accountCreated)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Sukces!</strong>
            <span class="block sm:inline">Twoje konto zostało pomyślnie utworzone. Dokumenty zostały wysłane na podany adres e-mail.</span>
        </div>
        <x-button wire:click="$set('accountCreated', false)" variant="primary" class="mt-4">Utwórz kolejne konto</x-button>
        <a href="{{ route('dashboard') }}" class="inline-block mt-4 ml-2 px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Przejdź do Dashboardu</a>
    @else
        <h2 class="text-2xl font-bold mb-4">Onboarding - Krok {{ $currentStep }} z 3</h2>

        @if ($errorMessage)
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Błąd!</strong>
                <span class="block sm:inline">{{ $errorMessage }}</span>
            </div>
        @endif

        @if ($currentStep === 1)
            <!-- Krok 1: Dane klienta + wybór produktu -->
            <form wire:submit.prevent="firstStepSubmit">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Imię i Nazwisko:</label>
                    <input type="text" wire:model.defer="name" id="name" placeholder="Twoje imię i nazwisko"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Adres E-mail:</label>
                    <input type="email" wire:model.defer="email" id="email" placeholder="Twoj adres e-mail"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('email') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Hasło:</label>
                    <input type="password" wire:model.defer="password" id="password" placeholder="Hasło"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('password') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-bold mb-2">Powtórz Hasło:</label>
                    <input type="password" wire:model.defer="password_confirmation" id="password_confirmation" placeholder="Powtórz hasło"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('password_confirmation') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="selectedProductCode" class="block text-gray-700 text-sm font-bold mb-2">Produkt Konta:</label>
                    <select wire:model="selectedProductCode" id="selectedProductCode"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        @foreach ($accountProducts as $product)
                            <option value="{{ $product->code }}">{{ $product->name }} ({{ $product->description }})</option>
                        @endforeach
                    </select>
                    @error('selectedProductCode') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                <x-button type="submit" variant="primary">Dalej</x-button>
            </form>
        @elseif ($currentStep === 2)
            <!-- Krok 2: Weryfikacja tożsamości -->
            <form wire:submit.prevent="secondStepSubmit">
                <div class="mb-4">
                    <label for="verificationData.document_number" class="block text-gray-700 text-sm font-bold mb-2">Numer Dokumentu (do weryfikacji):</label>
                    <input type="text" wire:model.defer="verificationData.document_number" id="verificationData.document_number" placeholder="Np. 12345678"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    @error('verificationData.document_number') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                    <p class="text-xs text-gray-500 mt-1">Użyj "12345678" dla pomyślnej symulacji weryfikacji.</p>
                </div>
                <x-button type="button" wire:click="$set('currentStep', 1)" variant="primary">Wstecz</x-button>
                <x-button type="submit" variant="primary">Zweryfikuj i Dalej</x-button>
            </form>
        @elseif ($currentStep === 3)
            <!-- Krok 3: Podsumowanie i aktywacja -->
            <h3 class="text-xl font-semibold mb-4">Potwierdzenie Danych</h3>
            <p class="mb-2">Imię i Nazwisko: <span class="font-medium">{{ $name }}</span></p>
            <p class="mb-2">Adres E-mail: <span class="font-medium">{{ $email }}</span></p>
            <p class="mb-2">Wybrany Produkt: <span class="font-medium">{{ $accountProducts->firstWhere('code', $selectedProductCode)->name ?? 'N/A' }}</span></p>
            <p class="mb-4">Numer Dokumentu (do weryfikacji): <span class="font-medium">{{ $verificationData['document_number'] }}</span></p>
            
            <p class="mb-4 text-green-600">Po kliknięciu "Zakończ Onboarding", Twoje konto zostanie utworzone, a dokumenty aktywacyjne zostaną wysłane na podany adres e-mail.</p>

            <x-button type="button" wire:click="$set('currentStep', 2)" variant="primary">Wstecz</x-button>
            <x-button wire:click="thirdStepSubmit" variant="primary">Zakończ Onboarding</x-button>
        @endif
    @endif
</div>
