# Hubsys Clearing API Contract (Mock Definition)

## 1. Cel
Niniejszy dokument definiuje kontrakt API (Application Programming Interface) do komunikacji z systemem Hubsys Clearing w celu inicjowania i przetwarzania transakcji międzybankowych. W obecnej fazie jest to mock definicji.

## 2. Architektura
Komunikacja będzie odbywać się za pośrednictwem HTTP/S. Format danych to JSON.
Autoryzacja: Token na okaziciela (Bearer Token) lub klucze API.

## 3. Endpointy

### 3.1. Inicjowanie Przelewu Wychodzącego (Outgoing Transfer)
`POST /api/v1/clearing/outgoing-transfer`

**Opis:** Inicjuje przelew z konta Nexa Bank do konta w innym banku (poprzez Hubsys Clearing). System Nexa Bank powinien wcześniej zarezerwować środki na koncie nadawcy.

**Żądanie (Request Body):**
```json
{
  "transfer_id": "UUID",                 // Unikalny identyfikator przelewu w systemie Nexa Bank
  "from_account_hrb": "string",          // HRB konta nadawcy (Nexa Bank)
  "to_account_hrb": "string",            // HRB konta odbiorcy (zewnętrzny bank)
  "amount": "integer",                   // Kwota przelewu w hubitach
  "currency": "string",                  // Waluta (np. "HUB")
  "title": "string",                     // Tytuł przelewu
  "reference_id": "string",              // Opcjonalny identyfikator referencyjny z systemu zewnętrznego
  "timestamp": "ISO 8601 string"         // Czas inicjacji przelewu
}
```

**Odpowiedź (Response Body - Sukces 202 Accepted):**
```json
{
  "status": "accepted",
  "clearing_reference_id": "UUID",       // Unikalny identyfikator przelewu w systemie Hubsys Clearing
  "message": "Transfer accepted for processing"
}
```

**Odpowiedź (Response Body - Błąd 4xx/5xx):**
```json
{
  "status": "error",
  "code": "string",                      // Kod błędu (np. "INVALID_HRB", "INSUFFICIENT_FUNDS_HUB", "CLEARTIME_EXCEEDED")
  "message": "string"                    // Szczegółowy opis błędu
}
```

### 3.2. Status Przelewu (Transfer Status)
`GET /api/v1/clearing/transfer-status/{clearing_reference_id}`

**Opis:** Pobiera aktualny status przelewu w systemie Hubsys Clearing.

**Odpowiedź (Response Body - Sukces 200 OK):**
```json
{
  "clearing_reference_id": "UUID",
  "status": "string",                    // Aktualny status: PENDING, PROCESSED, FAILED, RETURNED
  "last_update": "ISO 8601 string",
  "details": "string"                    // Opcjonalne szczegóły
}
```

### 3.3. Odbiór Przelewów Przychodzących (Incoming Transfer Webhook)
`POST /webhook/clearing/incoming-transfer`

**Opis:** Hubsys Clearing wysyła webhook do Nexa Bank po przetworzeniu przelewu przychodzącego dla konta w Nexa Bank.

**Żądanie (Request Body):**
```json
{
  "clearing_reference_id": "UUID",       // Identyfikator przelewu w systemie Hubsys Clearing
  "from_account_hrb": "string",          // HRB konta nadawcy (zewnętrzny bank)
  "to_account_hrb": "string",            // HRB konta odbiorcy (Nexa Bank)
  "amount": "integer",                   // Kwota przelewu w hubitach
  "currency": "string",                  // Waluta (np. "HUB")
  "title": "string",                     // Tytuł przelewu
  "bank_charge": "integer",              // Ewentualne opłaty bankowe (w hubitach)
  "processed_at": "ISO 8601 string"      // Czas przetworzenia przez Hubsys Clearing
}
```
**Odpowiedź (Response Body - Sukces 200 OK):**
```json
{
  "status": "success",
  "message": "Incoming transfer received and processed"
}
```

## 4. Modele Danych

### TransferStatus (Hubsys Clearing)
*   `PENDING`: Oczekuje na przetworzenie
*   `PROCESSED`: Przetworzony, środki przekazane
*   `FAILED`: Niepowodzenie przetworzenia
*   `RETURNED`: Przelew zwrócony do nadawcy

## 5. Uwagi
*   Wymagana obsługa idempotencji dla wszystkich endpointów POST.
*   Wszystkie kwoty powinny być przekazywane w hubitach (jednostka bazowa).
*   Waluta "HUB" jest domyślna.
