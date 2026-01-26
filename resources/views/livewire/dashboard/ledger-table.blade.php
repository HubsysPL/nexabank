<div>
    <h2 class="text-2xl font-bold mb-4 text-text-primary">Historia Operacji</h2>

    @if ($errorMessage)
        <div class="bg-alert-error-light-bg border-alert-error-light-border text-alert-error-light-text px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @elseif ($account)
        <div class="bg-surface shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-background">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Data
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Tytuł
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Typ Operacji
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Kierunek
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Kwota (HUB)
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">
                            Przeciwnik
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-surface divide-y divide-text-secondary">
                    @forelse ($ledgerEntries as $entry)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $entry->booked_at->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-primary">
                                {{ $entry->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $entry->operation_type }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $entry->direction->isCredit() ? 'text-secondary' : 'text-error' }}">
                                {{ $entry->direction->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-primary font-medium">
                                {{ \Modules\Core\ValueObjects\Money::fromHubits($entry->amount)->format() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                {{ $entry->counterparty_name ?? $entry->counterparty_account ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary text-center">
                                Brak historii operacji dla tego konta.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-4">
                {{ $ledgerEntries->links() }}
            </div>
        </div>
    @else
        <div class="bg-alert-warning-light-bg border-alert-warning-light-border text-alert-warning-light-text px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Brak Konta!</strong>
            <span class="block sm:inline">Nie znaleziono konta dla zalogowanego użytkownika.</span>
        </div>
    @endif
</div>
