# Nexa Bank - Roadmap & TODO (v2)

Dokument ten śledzi postęp prac nad MVP banku, opierając się na architekturze modułowej (`nwidart/laravel-modules`) i założeniach z historii konwersacji.

## Faza 0: Fundamenty i Architektura

- [x] Inicjalizacja projektu Laravel 12 + Livewire Starter Kit
- [x] Konfiguracja GitHub Actions (CI)
- [x] Konfiguracja `nwidart/laravel-modules`
- [x] **Branding**: Implementacja `tailwind.config.js` z kolorami i typografią z Brandingbooka.
- [x] **Struktura**: Utworzenie pustych modułów za pomocą `module:make`: `Core`, `Accounts`, `Ledger`, `Transfers`, `Clearing`.

---

## Faza 1: Core Banking Engine

### Moduł: Core

- [x] `Money` Value Object (przechowywanie w hubitach, operacje `add`/`subtract`).
- [x] **Testy**: Testy jednostkowe dla `Money` (dodawanie, odejmowanie, walidacja).
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
- [x] **Migracja**: Tabela `accounts` (bez `balance`!) z relacjami do `users` i `account_products`. Musi zawierać segmenty HRB (`bank_code`, `account_sequence` etc.).
- [ ] **Seeder**: Wypełnienie `account_products` produktami (np. STANDARD, BONUS100).
- [ ] **Logika**: Integracja `HrbGenerator` przy tworzeniu nowego konta.

### Moduł: Ledger (Źródło Prawdy)

- [x] **Migracja**: Tabela `ledger_entries` z kolumnami: `account_id`, `amount` (bigint), `direction`, `status`, `title`, `counterparty_name`, `counterparty_account`, `reference_id`.
- [ ] **Model**: `LedgerEntry` z poprawnymi `casts` na Enumy.
- [ ] **Serwis**: `LedgerService` z metodą `book()` do tworzenia wpisów w transakcji bazodanowej.
- [ ] **Logika**: Stworzenie mechanizmu obliczania salda na podstawie sumy wpisów w `ledger_entries` (Query lub metoda w repozytorium).

---

## Faza 2: Pierwsze Funkcje Biznesowe

### Moduł: Transfers

- [ ] **Migracja**: Tabela `transfers` do przechowywania intencji przelewu (`from_account_id`, `to_account_hrb`, `amount`, `title`, `status`).
- [ ] **Serwis**: `TransferService` z logiką:
    - `initiateInternalTransfer()`: tworzy wpis w `transfers` i od razu woła `LedgerService` do zaksięgowania.
    - `initiateInterbankTransfer()`: tworzy wpis w `transfers` i rezerwuje środki (tworzy `ledger_entries` ze statusem `PENDING`).
- [ ] **Testy**: Testy dla obu typów przelewów.

### Moduł: Clearing (Symulacja)

- [ ] **Migracja**: Tabela `clearing_batches`.
- [ ] **Zadanie Cron**: Stworzenie `Scheduled Command`, który:
    - Zbiera przelewy `PENDING`.
    - Tworzy `clearing_batch`.
    - Zmienia status wpisów w `ledger_entries` na `BOOKED`.

---

## Faza 3: Interfejs Użytkownika (Livewire)

- [ ] **Onboarding**: Komponent Livewire do tworzenia konta z wyborem produktu.
- [ ] **Dashboard**: Komponent Livewire wyświetlający dane konta (HRB) i saldo obliczone z Ledgera.
- [ ] **Historia**: Komponent Livewire `LedgerTable` do wyświetlania historii operacji z tabeli `ledger_entries`.
- [ ] **Przelewy**: Formularz Livewire do zlecania nowego przelewu.

---

## Faza 4: Integracje Zewnętrzne (Hubsys)

- [ ] **Mock**: Stworzenie `HubsysVerificationService` do symulacji weryfikacji tożsamości (HID, WDO).
- [ ] **Integracja**: Podpięcie serwisu pod proces onboardingu.
- [ ] **API**: Zdefiniowanie kontraktu API do komunikacji z prawdziwym Hubsys Clearing.

---

### Legenda

- [x] Zrobione
- [ ] Do zrobienia
