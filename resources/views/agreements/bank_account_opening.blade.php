<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umowa o Otwarcie Rachunku Bankowego</title>
    <style>
        body { font-family: 'Times New Roman', serif; line-height: 1.6; color: #333; }
        .container { width: 80%; margin: 20px auto; border: 1px solid #eee; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
        h1, h2 { text-align: center; color: #222; }
        p { margin-bottom: 10px; }
        .section { margin-top: 20px; }
        .footer { margin-top: 40px; text-align: center; font-size: 0.9em; color: #666; }
        .signature-line { border-bottom: 1px solid #ccc; width: 300px; margin-top: 60px; display: inline-block; }
        .signature-block { text-align: center; margin-top: 20px; }
        .clause { font-size: 0.8em; color: #999; margin-top: 30px; text-align: justify; }
        .highlight { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>UMOWA O OTWARCIE RACHUNKU BANKOWEGO</h1>
        <h2>Nexa Bank - Ekosystem Hubsys</h2>

        <p class="section">Zawarta dnia <span class="highlight">{{ $agreement_date }}</span> pomiędzy:</p>

        <p><span class="highlight">Nexa Bank Sp. z o.o.</span> (dalej "Bank"), z siedzibą w Fictional City, ul. Fikcyjna 1, zarejestrowana w Rejestrze Przedsiębiorców pod numerem KRS 0000000000, NIP 0000000000, reprezentowana przez [Imię i Nazwisko Osoby Reprezentującej Bank], a</p>

        <p><span class="highlight">{{ $user->name }}</span> (dalej "Klient"), PESEL/identyfikator: <span class="highlight">{{ $user->pesel ?? 'Brak danych' }}</span>, adres zamieszkania: [Adres Klienta], na podstawie Wirtualnego Dowodu Osobistego numer <span class="highlight">{{ $user->document_number ?? 'Brak danych' }}</span>, </p>

        <p class="section">§ 1. Przedmiot Umowy</p>
        <p>1. Przedmiotem niniejszej Umowy jest otwarcie i prowadzenie rachunku bankowego dla Klienta w Nexa Bank (dalej "Rachunek").</p>
        <p>2. Rachunek będzie prowadzony w walucie <span class="highlight">PLN</span>.</p>
        <p>3. Kod wybranego produktu: <span class="highlight">{{ $selectedProduct->name ?? 'Brak danych' }}</span>.</p>

        <p class="section">§ 2. Dane Klienta</p>
        <p>Dane Klienta w dniu zawarcia Umowy:</p>
        <ul>
            <li>Imię i Nazwisko: <span class="highlight">{{ $user->name }}</span></li>
            <li>Adres E-mail: <span class="highlight">{{ $user->email }}</span></li>
            <li>ID Klienta (Hubsys): <span class="highlight">{{ $user->customer_id }}</span></li>
            <li>Numer Wirtualnego Dowodu Osobistego: <span class="highlight">{{ $user->document_number ?? 'Brak danych' }}</span></li>
            <li>Numer HID (Hubsys Identity): <span class="highlight">{{ $user->hid ?? 'Brak danych' }}</span></li>
        </ul>

        <p class="section">§ 3. Postanowienia Końcowe</p>
        <p>1. Niniejsza Umowa wchodzi w życie z dniem jej podpisania przez obie strony.</p>
        <p>2. Wszelkie zmiany niniejszej Umowy wymagają formy pisemnej pod rygorem nieważności.</p>
        <p>3. Do spraw nieuregulowanych niniejszą Umową mają zastosowanie przepisy prawa polskiego.</p>
        <p>4. Umowa została sporządzona w dwóch jednobrzmiących egzemplarzach, po jednym dla każdej ze stron.</p>

        <div class="clause">
            <p><span class="highlight">KLAUZULA SPECJALNA:</span> Niniejsza umowa jest ważna wyłącznie w ekosystemie Hubsys i nie stanowi zobowiązania wobec prawdziwego banku komercyjnego. Nexa Bank działa w środowisku symulacyjnym Hubsys WDO i nie jest licencjonowanym bankiem w rozumieniu przepisów prawa bankowego.</p>
        </div>
        
        <div class="signature-block">
            <div class="signature-line"></div>
            <p><strong>Nexa Bank Sp. z o.o.</strong></p>
        </div>

        <div class="signature-block">
            <div class="signature-line"></div>
            <p><strong>Klient: {{ $user->name }}</strong></p>
            <p>Podpis elektroniczny (Profil WDO)</p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Nexa Bank. Wszelkie prawa zastrzeżone.</p>
        </div>
    </div>
</body>
</html>
