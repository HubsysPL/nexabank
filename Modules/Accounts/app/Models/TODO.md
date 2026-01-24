# Nexa Bank - Roadmap & TODO

Dokument ten śledzi postęp prac nad MVP banku, opierając się na architekturze modułowej i założeniach z README.

## 🟢 1. Infrastruktura i Konfiguracja (Core)

- [x] Inicjalizacja projektu Laravel 12
- [x] Konfiguracja GitHub Actions (CI) - Matrix PHP 8.4/8.5
- [x] Konfiguracja Pest PHP (Testy)
- [x] Setup Flux UI (credentials w ENV/CI)
- [ ] **Refactor**: Ujednolicenie wersji PHP w `tests.yml` (obecnie matrix 8.4/8.5, upewnić się co do wersji produkcyjnej)

## 🟡 2. Moduł: Accounts (Rachunki)

Status: _W trakcie rozwoju_

- [x] Model `Account` (podstawowa struktura)
- [x] Enum `AccountStatus`
- [ ] **Migracja**: Rozbudowa tabeli `accounts`. Brakuje kluczowych kolumn:
    - `account_number` (string, unikalny, format NRB)
    - `balance` (bigint, przechowywanie w groszach)
    - `currency` (string, domyślnie PLN)
    - `name` (nazwa rachunku, np. "Konto Osobiste")
- [ ] **Logika**: Generator numerów rachunków (algorytm IBAN/NRB z sumą kontrolną)
- [ ] **Factory**: `AccountFactory` do testów
- [ ] **Testy**: Testy jednostkowe dla modelu `Account` i generowania numerów

## 🔴 3. Moduł: Transfers (Przelewy Wewnętrzne)

Status: _Do zrobienia_

- [ ] Utworzenie struktury modułu `Transfers`
- [ ] **Migracja**: Tabela `transfers`
    - `sender_account_id`
    - `recipient_account_id`
    - `amount` (w groszach)
    - `title`, `receiver_name`
    - `executed_at`
- [ ] **Service**: `TransferService`
    - Obsługa transakcji bazodanowych (DB Transaction)
    - Blokowanie wierszy (lockForUpdate) przy aktualizacji salda
    - Wykorzystanie `InsufficientFundsException` (już istnieje w Core)

## 🔴 4. Moduł: Clearing (Elixir / Zewnętrzne)

Status: _Do zrobienia_

- [ ] Utworzenie struktury modułu `Clearing`
- [ ] Tabela sesji przychodzących/wychodzących
- [ ] Logika paczkowania przelewów do innych banków

## 🔴 5. Moduł: Users (Użytkownicy / Auth)

Status: _Do zrobienia_

- [ ] Integracja z modelem `Account` (relacja istnieje w kodzie, sprawdzić implementację po stronie User)
- [ ] Proces KYC (uproszczony dla MVP) - dane osobowe wymagane do założenia konta

## 🔴 6. Frontend (UI - Livewire + Flux)

Status: _Do zrobienia_

- [ ] Layout główny aplikacji (Dashboard)
- [ ] Widok szczegółów rachunku (Saldo, Historia)
- [ ] Formularz zlecenia przelewu

---

### Legenda

- [x] Zrobione
- [ ] Do zrobienia
