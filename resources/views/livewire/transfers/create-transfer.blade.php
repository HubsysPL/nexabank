<div>
    <h2 class="text-2xl font-bold mb-4">Zleć Nowy Przelew</h2>

    @if ($transferSuccess)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Sukces!</strong>
            <span class="block sm:inline">Twój przelew został pomyślnie zlecony.</span>
        </div>
    @endif

    @if ($errorMessage)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @endif

    <form wire:submit.prevent="createTransfer">
        <div class="mb-4">
            <label for="fromAccount" class="block text-gray-700 text-sm font-bold mb-2">Z Konta:</label>
            <select wire:model="fromAccount" id="fromAccount"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                @foreach ($userAccounts as $account)
                    <option value="{{ $account->id }}">{{ $account->hrb }} ({{ \Modules\Core\ValueObjects\Money::fromHubits($account->balance)->format() }} HUB)</option>
                @endforeach
            </select>
            @error('fromAccount') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="toAccountHrb" class="block text-gray-700 text-sm font-bold mb-2">Do (HRB Odbiorcy):</label>
            <input type="text" wire:model.defer="toAccountHrb" id="toAccountHrb" placeholder="Wprowadź HRB odbiorcy"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('toAccountHrb') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="amount" class="block text-gray-700 text-sm font-bold mb-2">Kwota (HUB):</label>
            <input type="number" wire:model.defer="amount" id="amount" step="0.01" min="0.01" placeholder="0.00"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('amount') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label for="title" class="block text-gray-700 text-sm font-bold mb-2">Tytuł Przelewu:</label>
            <input type="text" wire:model.defer="title" id="title" placeholder="Wprowadź tytuł przelewu"
                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            @error('title') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
        </div>

        <x-button type="submit" variant="primary">Zleć Przelew</x-button>
    </form>
</div>
