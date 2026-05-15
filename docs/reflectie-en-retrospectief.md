# Reflectie en retrospectief - NAVLOG

## 1. Opdrachtomschrijving

Voor dit project heb ik een NAVLOG-applicatie gemaakt met PHP, MySQL, PDO en OOP. De applicatie is bedoeld om flights en bijbehorende legs te beheren. Een gebruiker kan een flight aanmaken of selecteren, legs toevoegen, bewerken en verwijderen, aircraft- en timinggegevens opslaan, weerinformatie ophalen en een fuel calculation uitvoeren.

Het doel van het project was om de bestaande NAVLOG-structuur uit te breiden met een databasekoppeling en een werkende GUI. De gegevens worden opgeslagen in MySQL en opgehaald via PDO. De legs worden vanuit de database omgezet naar objecten met de `Leg` class en beheerd met de `LegArray` class.

## 2. Koppeling met de opdracht

De opdracht vraagt om een NAVLOG-applicatie waarin PHP, MySQL, PDO en OOP centraal staan. In mijn project is `index.php` bewust klein gehouden als bootstrap-bestand. De paginaweergave staat in `views/navlog-page.php` en de belangrijkste logica is verdeeld over class-bestanden in mappen zoals `Application`, `Controllers`, `Builders`, `Renderers`, `Models`, `Database`, `Helpers` en `Services`.

De GUI is gebaseerd op het aangeleverde NAVLOG-raamwerk. De interface is uitgebreid waar dat nodig was om databasefunctionaliteit, validatie, CRUD-acties, modals, fuel calculation en weather data mogelijk te maken. CSS en JavaScript zijn gebruikt als frontend-assets voor layout, dropdowns, modals, printweergave, Light/Dark Mode, Step guide en kleine gebruikersinteracties.

De opdracht benoemt dat de applicatie volgens OOP-concepten moet werken. In mijn project wordt een databaseleg omgezet naar een `Leg` object. Meerdere `Leg` objecten worden beheerd via `LegArray`. De databaseacties staan in `classes/Database/Database.php`, zodat databaseverantwoordelijkheid niet direct door de GUI wordt afgehandeld. Request-afhandeling, validatie, view-data en rendering zijn ook ondergebracht in aparte classes.

## 3. Wat ik heb gebouwd

Ik heb een webapplicatie gebouwd waarin de gebruiker eerst een flight selecteert of aanmaakt. Daarna worden de gegevens van die geselecteerde flight geladen in de interface. Bij een geselecteerde flight kan de gebruiker legs toevoegen, bewerken en verwijderen. De opgeslagen legs worden in de NAVLOG-tabel weergegeven.

Daarnaast heb ik extra onderdelen toegevoegd, zoals aircraft- en timinggegevens, METAR/TAF-weerinformatie, een fuel calculation, een graphical leg view, een 1:60 correction calculator, custom delete modals, validaties, screenshots, een database-export en een README.

Belangrijke onderdelen van de applicatie zijn:

- Flights aanmaken, selecteren, bewerken en verwijderen.
- Legs toevoegen, bewerken en verwijderen.
- Legs per geselecteerde flight laden uit de database.
- NAVLOG-tabel vullen met databasegegevens.
- OOP-structuur met `Leg` en `LegArray`.
- PDO-databaseverbinding via `Database.php`.
- Aircraft- en timinggegevens opslaan per flight.
- METAR/TAF-weerinformatie ophalen met `WeatherScraper.php`.
- Fuel calculation uitvoeren.
- Graphical leg view tonen op basis van de geselecteerde flight en eerste geladen leg.
- 1:60 correction calculator gebruiken met slider, off-track, closing angle en course correction.
- Server-side validatie.
- Custom confirmation modals voor delete-acties.
- Light/Dark Mode, Step guide en printweergave.

## 4. MUST-eisen

| School-eis | Uitwerking in mijn project |
|---|---|
| Een set legs kunnen weergeven in de huidige GUI vanuit de database | Legs worden opgehaald via `Database.php`, omgezet naar `Leg` objecten en via `LegArray` weergegeven in de NAVLOG-tabel. |
| Een leg invullen en alle waardes laten aansluiten op het NAVLOG/Excel-schema | Het Add/Edit Leg-formulier bevat velden zoals checkpoint, frequency, time, MEF, cruise, MH, variation, TH, WCA, wind, TT, distance en GS. |
| Een set legs kunnen opslaan in de database | Nieuwe en aangepaste legs worden opgeslagen in MySQL via PDO-methodes in `Database.php`. |
| Eerder opgeslagen legs kunnen inladen | De gebruiker selecteert een bestaande flight via Load saved flight. Daarna worden de gekoppelde legs uit de database geladen. |
| Database class gebruiken | `classes/Database/Database.php` regelt de databaseverbinding en de database-acties voor flights, legs, checkpoints, aircraft data en koppeltabellen. |
| OOP toepassen | `Leg.php` vertegenwoordigt één navigatieleg. `LegArray.php` beheert meerdere legs en ondersteunt totalen/accumulaties. |
| GUI behouden en functioneel maken | Het aangeleverde NAVLOG-raamwerk is behouden en uitgebreid met formulieren, panels, modals en validaties. |

## 5. SHOULD-eisen

| School-eis / extra onderdeel | Uitwerking in mijn project |
|---|---|
| KNMI scraper class | `WeatherScraper.php` haalt METAR/TAF-data op en toont onder andere windrichting en windsnelheid op basis van een ICAO-code. |
| Fuel calculation tabel en codelogica | De applicatie bevat een fuel calculation panel met invoervelden, berekende total required fuel, remaining fuel en fuel status. |
| 1:60 correction / meetpuntberekening | De applicatie bevat een interactieve 1:60 correction calculator met slider, off-track, closing angle, course correction en een tabel tot 5 NM. Deze is via het linkermenu en de graphical leg view te openen en blijft zichtbaar in de printweergave. |
| Printweergave | De NAVLOG-tabellen kunnen worden geprint zonder menu, knoppen en formulieren. |
| Light/Dark Mode | De interface heeft een Light/Dark Mode als extra gebruikersoptie. |
| Step guide | De applicatie bevat een Step guide die de gebruiker door belangrijke invoervelden begeleidt. |
| Custom modals | Delete-acties gebruiken custom confirmation modals in plaats van standaard browsermeldingen. |

## 6. Technische keuzes

### PHP en MySQL

Ik heb PHP gebruikt voor de backend en MySQL voor de database. De applicatie draait lokaal via XAMPP. MySQL Workbench is gebruikt om de database te beheren en een database-export te maken.

### PDO

Voor de databaseverbinding heb ik PDO gebruikt. Dit is veiliger en netter dan losse SQL zonder prepared statements. In `classes/Database/Database.php` staan de methodes voor het ophalen, opslaan, bewerken en verwijderen van data.

### OOP

De `Leg` class vertegenwoordigt één navigatieleg. Deze class bevat basisgegevens van een leg en berekent onder andere WCA, TH, MH en ground speed. De `LegArray` class beheert meerdere `Leg` objecten, telt tijd en afstand op en kan de objecten omzetten naar arrays voor de GUI. Hiermee sluit het project aan op de opdracht waarin `Leg` en `LegArray` centraal staan.

### Projectstructuur

Tijdens het refactoren heb ik de code opgesplitst in duidelijke mappen. `index.php` start alleen de applicatie. De view staat in `views/navlog-page.php`. De applicatieflow staat in `classes/Application`, de user actions staan in `classes/Controllers`, de view-data wordt voorbereid in `classes/Builders` en grotere HTML-onderdelen worden opgebouwd door classes in `classes/Renderers`. Hierdoor is de code beter gescheiden en is duidelijker te zien welk onderdeel welke verantwoordelijkheid heeft.

### Frontend

De frontend bestaat uit HTML, CSS en JavaScript. De CSS is gebruikt voor de layout, panels, tabellen, modals en printweergave. JavaScript is gebruikt voor dropdownlogica, aircraft type koppeling, fuel calculation, de 1:60 correction calculator, step guide, success messages, delete modals, printen en Light/Dark Mode.

## 7. Database en datastructuur

De database bevat tabellen voor flights, legs, checkpoints, aircraft data en de koppeling tussen flights en aircraft. Een flight kan meerdere legs hebben. Elke leg is gekoppeld aan een checkpoint. Aircraft- en timinggegevens worden gekoppeld aan een selected flight.

De database-export staat in:

```text
database/navlog.sql
```

De export bevat een schone demonstratiedatabase met twee flights, gekoppelde aircraft data, checkpoints en legs. Testrommel is verwijderd zodat de database geschikt is voor inlevering.

## 8. Validatie

Ik heb server-side validatie toegevoegd zodat verkeerde invoer niet wordt opgeslagen. De validatie controleert onder andere:

- ICAO-codes.
- Verplichte velden.
- Numerieke waarden.
- Waarden binnen toegestane bereiken.
- Aircraft/timingvelden zoals OAT, IAS en tacho.
- Legvelden zoals headings, windgegevens, tijd en afstand.

Bij fouten blijft het juiste panel open en wordt de foutmelding bij het juiste formulier getoond. Foute velden worden leeggemaakt en correcte velden blijven waar mogelijk ingevuld.

## 9. Testverslag

Ik heb de applicatie getest op de belangrijkste functies.

| Test | Resultaat |
|---|---|
| Flight aanmaken | Werkt |
| Flight selecteren | Werkt |
| Flight bewerken | Werkt |
| Flight verwijderen | Werkt met custom modal |
| Leg toevoegen | Werkt |
| Leg bewerken | Werkt |
| Leg verwijderen | Werkt met custom modal |
| Add/Edit leg validatie | Werkt |
| Aircraft/timing opslaan | Werkt |
| Aircraft/timing validatie | Werkt |
| METAR ophalen | Werkt |
| TAF ophalen | Werkt |
| Fuel calculation | Werkt |
| Graphical leg view | Werkt |
| 1:60 correction calculator | Werkt |
| Light/Dark Mode | Werkt |
| Step guide | Werkt |
| Printweergave | Werkt |
| Database-export importeren | Werkt |

## 10. Screenshots

De screenshots staan in de map:

```text
screenshots/
```

De map bevat de volgende screenshots:

- `01-selected-flight.png` - geselecteerde flight met geladen gegevens.
- `02-manage-flight.png` - beheren van de geselecteerde flight.
- `03-aircraft-timing.png` - aircraft- en timinggegevens per flight.
- `04-add-edit-leg-crud.png` - NAVLOG-tabel waarin legs direct toegevoegd, bewerkt, opgeslagen en verwijderd kunnen worden.
- `05-delete-leg-modal.png` - custom delete modal voor het verwijderen van een leg.
- `06-validation-example.png` - voorbeeld van server-side validatie.
- `07-weather-metar-taf.png` - METAR/TAF-weerinformatie.
- `08-fuel-calculation.png` - fuel calculation panel met berekende total required fuel, remaining fuel en fuel status.
- `09-light-dark-mode.png` - Light/Dark Mode naast elkaar weergegeven.
- `10-step-guide.png` - Step guide met uitleg bij een belangrijk invoerveld.
- `11-print-view.png` - opgeschoonde printweergave met aircraft timing en NAVLOG-tabel.

## 11. Reflectie

Tijdens dit project heb ik veel geleerd over het combineren van PHP, MySQL, PDO en OOP in één werkende applicatie. Vooral het koppelen van databasegegevens aan objecten was belangrijk. Ik heb geleerd dat het niet genoeg is om data alleen op te slaan; de applicatie moet ook logisch blijven werken vanuit de geselecteerde flight.

Een lastig onderdeel was het goed laten samenwerken van de backend, database en frontend. Als een flight geselecteerd is, moeten alle onderdelen dezelfde flight blijven gebruiken. Dit was belangrijk bij legs, aircraft/timing data, fuel calculation en redirects na formulieren.

Ook validatie was een belangrijk leerpunt. In het begin konden sommige verkeerde waarden nog worden opgeslagen. Later heb ik de validatie verbeterd zodat lege of ongeldige velden worden geblokkeerd voordat ze de database bereiken.

Later in het project heb ik de structuur verder opgeschoond. Eerst stond er nog veel PHP en HTML samen in `index.php`. Uiteindelijk heb ik `index.php` teruggebracht tot een korte bootstrap en heb ik de paginaweergave, renderlogica, request handling en view-data voorbereiding verdeeld over aparte classes. Dit maakte het project groter qua aantal bestanden, maar het sluit beter aan op de OOP-eis van de opdracht.

Ik heb ook geleerd dat gebruikersinterface en gebruiksgemak belangrijk zijn. Daarom zijn de panels gesplitst, is de kleine blauwe tabel readonly gemaakt, zijn delete-acties voorzien van custom modals, is de graphical leg view toegevoegd en is de printweergave opgeschoond.

## 12. Retrospectief

### Wat ging goed?

- De basisfunctionaliteit met flights en legs werkt.
- De databasekoppeling met PDO werkt.
- De OOP-structuur met `Leg` en `LegArray` is duidelijker geworden.
- Validaties zijn verbeterd.
- De interface is overzichtelijker gemaakt.
- De graphical leg view en 1:60 correction calculator sluiten beter aan op het aangeleverde NAVLOG-design.
- De projectmap bevat README, screenshots en een database-export.
- De code is uiteindelijk gestructureerd in duidelijke OOP-mappen zoals Application, Controllers, Builders, Renderers, Models en Database.

### Wat was lastig?

- Het behouden van de selected flight na formulieracties.
- Het opslaan en tonen van aircraft/timing data.
- Het voorkomen van foutieve database-invoer.
- Het consistent maken van de interface.
- Het netjes maken van de printweergave.
- Het opsplitsen van `index.php` zonder bestaande functionaliteit te breken.

### Wat zou ik volgende keer verbeteren?

Als ik dit project opnieuw zou doen, zou ik eerder beginnen met een duidelijkere projectstructuur. Ik zou de frontend en backend vanaf het begin beter scheiden en eerder nadenken over de database-relaties. Ook zou ik eerder een vaste validatiestructuur maken, zodat alle formulieren op dezelfde manier werken.

Daarnaast zou ik de JavaScript-data zoals airports, aircraft en frequencies later liever uit een database of configuratiebestand halen in plaats van hardcoded in JavaScript. Voor dit project was het voldoende, maar voor een grotere applicatie is dat minder flexibel.

## 13. Conclusie

Ik heb een werkende NAVLOG-applicatie gemaakt met PHP, MySQL, PDO en OOP. De applicatie ondersteunt flightbeheer, legbeheer, databaseopslag, validatie, weather data, fuel calculation, graphical leg view, 1:60 correction, screenshots, een database-export en een nette README. Door de uiteindelijke mappenstructuur met Application, Controllers, Builders, Renderers, Models en Database is ook duidelijk zichtbaar dat de applicatie volgens OOP is opgebouwd. Het project is geschikt om in te leveren en laat zien dat ik backend, database, OOP en frontend kan combineren in één werkende applicatie.