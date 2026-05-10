# NAVLOG

NAVLOG is een PHP/MySQL-webapplicatie voor het beheren van navigatielogs. Met de applicatie kan de gebruiker flights aanmaken en selecteren, legs beheren per geselecteerde flight, de NAVLOG-tabel bekijken, METAR/TAF-weerinformatie ophalen en een fuel calculation uitvoeren.

Dit project is gebouwd met PHP, MySQL, PDO en OOP.

## Functionaliteiten

- Flights aanmaken, selecteren, bewerken en verwijderen.
- Legs toevoegen, bewerken en verwijderen voor een geselecteerde flight.
- Opgeslagen legs uit de database laden en tonen in de NAVLOG-tabel.
- `Leg` en `LegArray` classes gebruiken voor de OOP-structuur.
- KNMI/METAR winddata ophalen op basis van een ICAO-code.
- TAF forecastinformatie ophalen op basis van een ICAO-code.
- Aircraft- en timinggegevens opslaan en tonen voor de geselecteerde flight.
- Fuel calculation uitvoeren voor de geselecteerde flight.
- Het NAVLOG-overzicht printen.
- Light/Dark Mode gebruiken.
- Server-side validatie gebruiken voor flights, legs, ICAO-codes en numerieke invoer.
- Custom confirmation modals gebruiken voor delete-acties.

## Gebruikte technieken

- PHP 8
- MySQL
- PDO
- OOP
- HTML
- CSS
- JavaScript
- XAMPP
- MySQL Workbench
- Git & GitHub

## Projectstructuur

```text
Navlog-School/
├── classes/
│   ├── Leg.php
│   ├── LegArray.php
│   └── WeatherScraper.php
├── css/
│   └── style.css
├── js/
│   └── script.js
├── database/
│   └── navlog.sql
├── Database.php
├── index.php
└── README.md
```

## Installatie

Volg deze stappen om het project lokaal te installeren en te starten.

### 1. Projectmap plaatsen

Plaats de projectmap in de `htdocs` map van XAMPP:

```text
/Applications/XAMPP/xamppfiles/htdocs/Navlog-School
```

### 2. XAMPP starten

Start XAMPP en zet de volgende services aan:

- Apache
- MySQL

### 3. Database importeren

Open MySQL Workbench en importeer het databasebestand:

```text
database/navlog.sql
```

Het SQL-bestand maakt zelf de database aan met:

```sql
CREATE DATABASE IF NOT EXISTS `navlog_school`;
USE `navlog_school`;
```

Na het importeren bevat de database twee demo-flights met bijbehorende aircraft data, checkpoints en legs.

### 4. Databaseverbinding controleren

Controleer in `Database.php` of de databasegegevens overeenkomen met je lokale XAMPP-installatie:

```php
private string $host = 'localhost';
private string $database = 'navlog_school';
private string $username = 'root';
private string $password = '';
```

Bij een standaard XAMPP-installatie op macOS zijn deze gegevens meestal correct.

### 5. Applicatie openen

Open de applicatie in de browser:

```text
http://localhost/Navlog-School/index.php
```

## Gebruik

### 1. Flight laden

Gebruik `Load saved flight` om een bestaande flight te selecteren. Na het selecteren worden de flightgegevens, aircraftgegevens en gekoppelde legs geladen.

### 2. Flight beheren

Open `Manage selected flight` om de geselecteerde flight te bewerken. Hier kun je onder andere de datum, departure, destination, elevations, altitudes en TAS aanpassen.

### 3. Aircraft en timing beheren

Open `Manage aircraft and timing data` om aircraft- en timinggegevens op te slaan, zoals pilot, registration, aircraft type, OAT, IAS, tacho, off-blocks, engine off, take-off time en landing time.

### 4. Legs beheren

Open `Add leg to selected flight` om een nieuwe leg toe te voegen. In de NAVLOG-tabel kun je bestaande legs bewerken of verwijderen met de Edit- en Delete-knoppen.

### 5. Weather data gebruiken

Gebruik het METAR/TAF-gedeelte om weerinformatie op te halen met een ICAO-code, bijvoorbeeld:

```text
EHRD
EHAM
```

### 6. Fuel calculation gebruiken

Open `Fuel calculation`, vul de fuelvelden in en klik op `Calculate fuel`. De applicatie toont daarna total required fuel, remaining fuel en de fuel status.

### 7. Printen

Klik op `Print` om een schone printweergave van de NAVLOG-tabellen te openen. Menu's, knoppen en formulieren worden niet meegeprint.

## Belangrijke bestanden

- `index.php` – verwerkt acties, validatie en de hoofdinterface van NAVLOG.
- `Database.php` – bevat de PDO-databaseverbinding en databasequeries.
- `classes/Leg.php` – class voor één navigatieleg.
- `classes/LegArray.php` – beheert meerdere `Leg` objecten.
- `classes/WeatherScraper.php` – haalt METAR- en TAF-data op.
- `css/style.css` – bevat de styling van de applicatie.
- `js/script.js` – bevat frontendlogica voor dropdowns, modals, fuel calculation, printen, Light/Dark Mode en invoerhulp.
- `database/navlog.sql` – database-export om de projectdatabase te importeren.

## Database

De applicatie gebruikt databasetabellen voor flights, legs, checkpoints en aircraft data. Een flight kan meerdere legs hebben. De geselecteerde flight bepaalt welke legs en aircraftgegevens in de interface worden geladen.

De database-export staat in:

```text
database/navlog.sql
```

## Validatie

De applicatie gebruikt server-side validatie om te voorkomen dat ongeldige data wordt opgeslagen. Validatie wordt gebruikt voor:

- Flightgegevens.
- Aircraft- en timinggegevens.
- Leggegevens.
- ICAO-codes.
- Numerieke velden.
- Fuel calculation velden.

Ongeldige velden worden leeggemaakt en geldige velden blijven waar mogelijk ingevuld. Foutmeldingen worden getoond bij het formulier waar de fout is ontstaan.

## Auteur

Vishal Tewari  
MBO 4 Software Developer  
Techniek College Rotterdam