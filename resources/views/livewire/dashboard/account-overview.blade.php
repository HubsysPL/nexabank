<div>
    <h2 class="text-2xl font-bold mb-4 text-text-primary">Przegląd Konta</h2>

    @if ($errorMessage)
        <div class="bg-alert-error-light-bg border-alert-error-light-border text-alert-error-light-text px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Błąd!</strong>
            <span class="block sm:inline">{{ $errorMessage }}</span>
        </div>
    @elseif ($account)
        <div class="bg-surface shadow overflow-hidden sm:rounded-lg mb-4">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-text-primary">Dane Konta</h3>
            </div>
            <div class="border-t border-text-secondary">
                <dl>
                    <div class="bg-background px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-text-secondary">Numer Rachunku (HRB)</dt>
                        <dd class="mt-1 text-sm text-text-primary sm:mt-0 sm:col-span-2">{{ $account->hrb }}</dd>
                    </div>
                    <div class="bg-surface px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-text-secondary">Nazwa Produktu</dt>
                        <dd class="mt-1 text-sm text-text-primary sm:mt-0 sm:col-span-2">{{ $account->product->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="bg-background px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-text-secondary">Status Konta</dt>
                        <dd class="mt-1 text-sm text-text-primary sm:mt-0 sm:col-span-2">{{ $account->status->name }}</dd>
                    </div>
                    <div class="bg-surface px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-text-secondary">Dostępne Saldo</dt>
                        <dd class="mt-1 text-xl font-bold text-text-primary sm:mt-0 sm:col-span-2">{{ $balance }} HUB</dd>
                    </div>
                </dl>
            </div>
        </div>
    @else
        <div class="bg-alert-warning-light-bg border-alert-warning-light-border text-alert-warning-light-text px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Brak Konta!</strong>
            <span class="block sm:inline">Nie znaleziono konta dla zalogowanego użytkownika.</span>
        </div>
    @endif
</div>
