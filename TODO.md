# Nexa Bank - Roadmap & TODO (v2)

Dokument ten śledzi postęp prac nad MVP banku, opierając się na architekturze modułowej (`nwidart/laravel-modules`) i założeniach z historii konwersacji.

## Faza 0: Fundamenty i Architektura

- [x] Inicjalizacja projektu Laravel 12 + Livewire Starter Kit
- [ ] Konfiguracja GitHub Actions (CI)
- [x] Konfiguracja `nwidart/laravel-modules`
- [x] **Branding**: Implementacja `tailwind.config.js` z kolorami i typografią z Brandingbooka.
- [x] **Struktura**: Utworzenie pustych modułów za pomocą `module:make`: `Core`, `Accounts`, `Ledger`, `Transfers`, `Clearing`. (Moduł Users jest rozszerzeniem domyślnego).

---

## Faza 1: Core Banking Engine

### Moduł: Core

- [x] `Money` Value Object (przechowywanie w hubitach, operacje `add`/`subtract`).
- [ ] **Testy**: Testy jednostkowe dla `Money` (dodawanie, odejmowanie, walidacja). *Fix Money.php do zrobienia.*
- [x] **Enumy**: Stworzenie Enumów dla statusów (`AccountStatus`, `LedgerStatus`, `TransferStatus`).
- [x] **Wyjątki**: Stworzenie wyjątków domenowych (`InsufficientFundsException`, `AccountBlockedException`).
- [x] **Generatory**:
    - `ClientNumberGenerator` (CID - numeryczny, 8-10 cyfr, unikalny).
    - `HrbService` (dla numerów rachunków w standardzie HRB).

### Moduł: Users (rozszerzenie domyślnego)

- [x] **Migracja**: Dodanie kolumny `customer_id` (CID) do tabeli `users`.
- [x] **Logowanie**: Modyfikacja logiki logowania, aby używać `customer_id` jako identyfikatora.
- [x] **Rejestracja**: Integracja `ClientNumberGenerator` z procesem tworzenia użytkownika.

### Moduł: Accounts

- [x] **Migracja**: Tabela `account_products` (`code`, `name`, `description`, `rules`).
- [x] **Migracja**: Tabela `accounts` (z kolumną `balance` jako cache) z relacjami do `users` i `account_products`. Musi zawierać segmenty HRB (`bank_code`, `account_sequence` etc.).
- [x] **Seeder**: Wypełnienie `account_products` produktami (np. STANDARD, BONUS100).
- [x] **Logika**: Integracja `HrbGenerator` przy tworzeniu nowego konta.

### Moduł: Ledger (Źródło Prawdy)

- [x] **Migracja**: Tabela `ledger_entries` (uwzględniająca nową migrację z brakującymi polami i indeksami).
- [x] **Model**: `LedgerEntry` z poprawnymi `casts` na Enumy.
- [x] **Serwis**: `LedgerService` z metodą `bookInternalTransfer()`.
- [x] **Logika**: Stworzenie mechanizmu obliczania salda na podstawie sumy wpisów w `ledger_entries` (Query lub metoda w repozytorium).

---

## Faza 2: Pierwsze Funkcje Biznesowe

### Moduł: Transfers

- [x] **Migracja**: Tabela `transfers` do przechowywania intencji przelewu (`from_account_id`, `to_account_hrb`, `amount`, `title`, `status`).
- [x] **Serwis**: `TransferService` z logiką:
    - `initiateInternalTransfer()`: tworzy wpis w `transfers` i od razu woła `LedgerService` do zaksięgowania.
    - `initiateInterbankTransfer()`: tworzy wpis w `transfers` i rezerwuje środki (tworzy `ledger_entries` ze statusem `PENDING`).
- [ ] **Testy**: Testy dla obu typów przelewów.

### Moduł: Clearing (Symulacja)

- [x] **Migracja**: Tabela `clearing_batches`.
- [x] **Zadanie Cron**: Stworzenie `Scheduled Command`, który:
    - Zbiera przelewy `PENDING`.
    - Tworzy `clearing_batch`.
    - Zmienia status wpisów w `ledger_entries` na `BOOKED`.

---

## Faza 3: Interfejs Użytkownika (Livewire)

- [x] **Onboarding**: Komponent Livewire do tworzenia konta z wyborem produktu.
- [x] **Dashboard**: Komponent Livewire wyświetlający dane konta (HRB) i saldo obliczone z Ledgera.
- [x] **Historia**: Komponent Livewire `LedgerTable` do wyświetlania historii operacji z tabeli `ledger_entries`.
- [x] **Przelewy**: Formularz Livewire do zlecania nowego przelewu.

---

## Faza 4: Integracje Zewnętrzne (Hubsys)

- [x] **Mock**: Stworzenie `HubsysVerificationService` do symulacji weryfikacji tożsamości (HID, WDO).
- [x] **Integracja**: Podpięcie serwisu pod proces onboardingu.
- [x] **API**: Zdefiniowanie kontraktu API do komunikacji z prawdziwym Hubsys Clearing.

---

## Kolejne Pilne Kroki:

1.  **Uruchomienie Migracji**: Wykonaj `php artisan migrate`, aby zastosować wszystkie zmiany w schemacie bazy danych.
2.  **Testy Jednostkowe `LedgerService`**: Stwórz testy, aby zweryfikować poprawność działania serwisu księgowego.

---

### Legenda

- [x] Zrobione
- [ ] Do zrobienia