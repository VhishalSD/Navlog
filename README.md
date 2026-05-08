# NAVLOG

**Navigatielog webapplicatie** – PHP, MySQL, PDO & OOP schoolproject  
MBO 4 Software Developer

## Over het project

NAVLOG is een webapplicatie waarmee een navigatielog kan worden beheerd. De gebruiker selecteert of maakt eerst een **flight** aan. Daarna kunnen bij die flight **legs** worden toegevoegd en bekeken in de NAVLOG-tabel.

De applicatie gebruikt **PHP**, **MySQL**, **PDO** en **OOP**. Databasegegevens worden opgehaald via PDO en daarna omgezet naar `Leg` objecten die worden beheerd met `LegArray`.

## Functionaliteiten

- Flights aanmaken en selecteren.
- Legs toevoegen per geselecteerde flight.
- Legs vanuit de database laden en tonen in de NAVLOG-tabel.
- `Leg` en `LegArray` gebruiken voor OOP-structuur.
- KNMI METAR winddata ophalen op basis van ICAO-code.
- TAF forecast ophalen op basis van ICAO-code.
- Fuel calculation uitvoeren voor de geselecteerde flight.
- Gescheiden frontend-bestanden: CSS in `css/style.css` en JavaScript in `js/script.js`.
- Printfunctie voor het NAVLOG-overzicht.
- Light/Dark Mode voor de interface.
- Stappenplan dat de gebruiker door de belangrijkste invoervelden leidt.
- Server-side validatie voor flights, legs, ICAO-codes en numerieke invoer.

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

4. Importeer de NAVLOG-database in MySQL Workbench.
5. Controleer de databasegegevens in `Database.php`.
6. Open de applicatie in de browser:

```text
http://localhost/Navlog-School/index.php
```

## Belangrijke bestanden

- `index.php` – verwerkt de acties, toont de NAVLOG-interface en bevat server-side validatie.
- `Database.php` – bevat de databaseverbinding en queries via PDO.
- `classes/Leg.php` – class voor één navigatieleg.
- `classes/LegArray.php` – beheert meerdere `Leg` objecten.
- `classes/WeatherScraper.php` – haalt METAR en TAF data op.
- `css/style.css` – styling van de applicatie.
- `js/script.js` – frontendlogica zoals dropdowns, fuel calculation, print, Light/Dark Mode en stappenplan.

## Database

De applicatie gebruikt tabellen voor flights, checkpoints en legs. Een flight kan meerdere legs hebben. De geselecteerde flight bepaalt welke legs in de NAVLOG-tabel worden geladen.

De database-export hoort in `database/navlog.sql` te staan, zodat het project opnieuw kan worden geïmporteerd in MySQL Workbench.

## Schoolcriteria

| Eis | Status |
|---|---|
| Legs vanuit de database tonen in de GUI | Klaar |
| Leg invullen volgens het NAVLOG-schema | Klaar |
| Legs opslaan in de database | Klaar |
| Eerder opgeslagen legs inladen | Klaar |
| `Leg` en `LegArray` gebruiken | Klaar |
| KNMI METAR winddata ophalen | Klaar |
| TAF forecast ophalen | Klaar |
| Fuel calculation met logica | Klaar |
| Printfunctie | Klaar |
| Stappenplan voor invoerhulp | Klaar |
| Server-side validatie voor invoer | Klaar |

## Korte uitleg

Ik heb een PHP/MySQL NAVLOG-applicatie gemaakt waarin flights en legs via de database aan elkaar gekoppeld zijn. De databaseverbinding loopt via PDO. De `Leg` class gebruik ik voor één navigatieleg en de `LegArray` class gebruik ik om meerdere legs als objecten te beheren.

De interface is zo opgebouwd dat je eerst een flight selecteert of aanmaakt. Daarna kun je legs toevoegen, METAR/TAF-data ophalen, een fuel calculation uitvoeren en het overzicht printen. Ook bevat de applicatie een Light/Dark Mode, een stappenplan voor invoerhulp en server-side validatie om verkeerde invoer te blokkeren.

## Auteur

Vishal Tewari  
MBO 4 Software Developer  
Techniek College Rotterdam