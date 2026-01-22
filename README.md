# Nexa Bank

**Nexa Bank** to otwartoźródłowy projekt bankowości online, tworzony publicznie w trakcie live codingu.

To nie jest fintech ani aplikacja demonstracyjna.  
Projekt symuluje **realną architekturę bankową**, opartą o:

- rozliczenia paczkowe (ELIXIR-like),
- formalne standardy numerów rachunków,
- wyraźny podział odpowiedzialności systemów,
- stabilny, konserwatywny UI.

---

## 🎯 Cele projektu

- zbudowanie **działającego MVP banku**
- pokazanie, jak wygląda bank „od środka”
- edukacja z zakresu:
    - architektury systemów finansowych
    - backendu (Laravel)
    - projektowania API
    - bankowości transakcyjnej

---

## 🧱 Zakres MVP

- rachunek osobisty (1 użytkownik = 1 rachunek)
- standard numeru HRB
- przelewy wewnętrzne
- przelewy międzybankowe (clearing)
- historia operacji
- web + mobile (React Native)

---

## ⚙️ Stack technologiczny

- Laravel 12
- PHP 8.3+
- PostgreSQL
- Redis
- Livewire (web)
- React Native (mobile)
- Tailwind CSS

---

## 🧩 Architektura

Projekt oparty o **moduły**, rozwijane niezależnie:

- Core
- Accounts
- Transfers
- Clearing
- Users
- UI

Każdy moduł:

- posiada własne migracje
- własne serwisy
- własne API
- może być rozwijany niezależnie

---

## 📜 Status

Projekt jest:

- 🔧 w aktywnym rozwoju
- 🧪 eksperymentalny
- 🎓 edukacyjny

Nie jest bankiem komercyjnym.

---

## 🤝 Współpraca

Pull Requesty:

- mile widziane
- akceptowane po review
- preferowane od zaufanych osób

---

## 📺 Live Coding

Projekt rozwijany publicznie na YouTube:
👉 https://www.youtube.com/@Skowronkowy_dev

---

## 📄 Licencja

MIT
