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

1. Start XAMPP.
2. Start Apache en MySQL.
3. Plaats de projectmap in:

```text
/Applications/XAMPP/xamppfiles/htdocs/Navlog-School
```

4. Importeer het databasebestand in MySQL Workbench:

```text
database/navlog.sql
```

5. Controleer de database-instellingen in:

```text
Database.php
```

6. Open de applicatie in de browser:

```text
http://localhost/Navlog-School/index.php
```

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