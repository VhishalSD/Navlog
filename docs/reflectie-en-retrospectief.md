# Reflectie en retrospectief - NAVLOG

## 1. Opdrachtomschrijving

Voor dit project heb ik een NAVLOG-applicatie gemaakt met PHP, MySQL, PDO en OOP. De applicatie is bedoeld om flights en bijbehorende legs te beheren. Een gebruiker kan een flight aanmaken of selecteren, legs toevoegen, bewerken en verwijderen, aircraft- en timinggegevens opslaan, weerinformatie ophalen en een fuel calculation uitvoeren.

Het doel van het project was om de bestaande NAVLOG-structuur uit te breiden met een databasekoppeling en een werkende GUI. De gegevens worden opgeslagen in MySQL en opgehaald via PDO. De legs worden vanuit de database omgezet naar objecten met de `Leg` class en beheerd met de `LegArray` class.

## 2. Koppeling met de opdracht

De opdracht vraagt om een NAVLOG-applicatie waarin PHP, MySQL, PDO en OOP centraal staan. In mijn project is `index.php` de hoofdinterface van de applicatie. De belangrijkste logica is ondergebracht in class-bestanden zoals `Database.php`, `Leg.php`, `LegArray.php` en `WeatherScraper.php`.

De GUI is gebaseerd op het aangeleverde NAVLOG-raamwerk. De interface is uitgebreid waar dat nodig was om databasefunctionaliteit, validatie, CRUD-acties, modals, fuel calculation en weather data mogelijk te maken. CSS en JavaScript zijn gebruikt als frontend-assets voor layout, dropdowns, modals, printweergave, Light/Dark Mode, Step guide en kleine gebruikersinteracties.

De opdracht benoemt dat de applicatie volgens OOP-concepten moet werken. In mijn project wordt een databaseleg omgezet naar een `Leg` object. Meerdere `Leg` objecten worden beheerd via `LegArray`. De databaseacties staan in `Database.php`, zodat databaseverantwoordelijkheid niet direct door de GUI wordt afgehandeld.

## 3. Wat ik heb gebouwd

Ik heb een webapplicatie gebouwd waarin de gebruiker eerst een flight selecteert of aanmaakt. Daarna worden de gegevens van die geselecteerde flight geladen in de interface. Bij een geselecteerde flight kan de gebruiker legs toevoegen, bewerken en verwijderen. De opgeslagen legs worden in de NAVLOG-tabel weergegeven.

Daarnaast heb ik extra onderdelen toegevoegd, zoals aircraft- en timinggegevens, METAR/TAF-weerinformatie, een fuel calculation, custom delete modals, validaties, screenshots, een database-export en een README.

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
| Database class gebruiken | `Database.php` regelt de databaseverbinding en de database-acties voor flights, legs, checkpoints, aircraft data en koppeltabellen. |
| OOP toepassen | `Leg.php` vertegenwoordigt één navigatieleg. `LegArray.php` beheert meerdere legs en ondersteunt totalen/accumulaties. |
| GUI behouden en functioneel maken | Het aangeleverde NAVLOG-raamwerk is behouden en uitgebreid met formulieren, panels, modals en validaties. |

## 5. SHOULD-eisen

| School-eis / extra onderdeel | Uitwerking in mijn project |
|---|---|
| KNMI scraper class | `WeatherScraper.php` haalt METAR/TAF-data op en toont onder andere windrichting en windsnelheid op basis van een ICAO-code. |
| Fuel calculation tabel en codelogica | De applicatie bevat een fuel calculation panel met invoervelden, berekende total required fuel, remaining fuel en fuel status. |
| Printweergave | De NAVLOG-tabellen kunnen worden geprint zonder menu, knoppen en formulieren. |
| Light/Dark Mode | De interface heeft een Light/Dark Mode als extra gebruikersoptie. |
| Step guide | De applicatie bevat een Step guide die de gebruiker door belangrijke invoervelden begeleidt. |
| Custom modals | Delete-acties gebruiken custom confirmation modals in plaats van standaard browsermeldingen. |

## 6. Technische keuzes

### PHP en MySQL

Ik heb PHP gebruikt voor de backend en MySQL voor de database. De applicatie draait lokaal via XAMPP. MySQL Workbench is gebruikt om de database te beheren en een database-export te maken.

### PDO

Voor de databaseverbinding heb ik PDO gebruikt. Dit is veiliger en netter dan losse SQL zonder prepared statements. In `Database.php` staan de methodes voor het ophalen, opslaan, bewerken en verwijderen van data.

### OOP

De `Leg` class vertegenwoordigt één navigatieleg. Deze class bevat basisgegevens van een leg en berekent onder andere WCA, TH, MH en ground speed. De `LegArray` class beheert meerdere `Leg` objecten, telt tijd en afstand op en kan de objecten omzetten naar arrays voor de GUI. Hiermee sluit het project aan op de opdracht waarin `Leg` en `LegArray` centraal staan.

### Frontend

De frontend bestaat uit HTML, CSS en JavaScript. De CSS is gebruikt voor de layout, panels, tabellen, modals en printweergave. JavaScript is gebruikt voor dropdownlogica, aircraft type koppeling, fuel calculation, step guide, success messages, delete modals, printen en Light/Dark Mode.

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
| Light/Dark Mode | Werkt |
| Step guide | Werkt |
| Printweergave | Werkt |
| Database-export importeren | Werkt |

## 10. Screenshots

De screenshots staan in de map:

```text
screenshots/
```

De map bevat screenshots van onder andere:

- Selected flight.
- Manage selected flight.
- Manage aircraft and timing data.
- Add/Edit leg.
- NAVLOG-tabel met CRUD-acties.
- Delete leg modal.
- Validatievoorbeeld.
- METAR/TAF.
- Fuel calculation.
- Light/Dark Mode.
- Step guide.
- Printweergave.

## 11. Reflectie

Tijdens dit project heb ik veel geleerd over het combineren van PHP, MySQL, PDO en OOP in één werkende applicatie. Vooral het koppelen van databasegegevens aan objecten was belangrijk. Ik heb geleerd dat het niet genoeg is om data alleen op te slaan; de applicatie moet ook logisch blijven werken vanuit de geselecteerde flight.

Een lastig onderdeel was het goed laten samenwerken van de backend, database en frontend. Als een flight geselecteerd is, moeten alle onderdelen dezelfde flight blijven gebruiken. Dit was belangrijk bij legs, aircraft/timing data, fuel calculation en redirects na formulieren.

Ook validatie was een belangrijk leerpunt. In het begin konden sommige verkeerde waarden nog worden opgeslagen. Later heb ik de validatie verbeterd zodat lege of ongeldige velden worden geblokkeerd voordat ze de database bereiken.

Ik heb ook geleerd dat gebruikersinterface en gebruiksgemak belangrijk zijn. Daarom zijn de panels gesplitst, is de kleine blauwe tabel readonly gemaakt, zijn delete-acties voorzien van custom modals en is de printweergave opgeschoond.

## 12. Retrospectief

### Wat ging goed?

- De basisfunctionaliteit met flights en legs werkt.
- De databasekoppeling met PDO werkt.
- De OOP-structuur met `Leg` en `LegArray` is duidelijker geworden.
- Validaties zijn verbeterd.
- De interface is overzichtelijker gemaakt.
- De projectmap bevat README, screenshots en een database-export.

### Wat was lastig?

- Het behouden van de selected flight na formulieracties.
- Het opslaan en tonen van aircraft/timing data.
- Het voorkomen van foutieve database-invoer.
- Het consistent maken van de interface.
- Het netjes maken van de printweergave.

### Wat zou ik volgende keer verbeteren?

Als ik dit project opnieuw zou doen, zou ik eerder beginnen met een duidelijkere projectstructuur. Ik zou de frontend en backend vanaf het begin beter scheiden en eerder nadenken over de database-relaties. Ook zou ik eerder een vaste validatiestructuur maken, zodat alle formulieren op dezelfde manier werken.

Daarnaast zou ik de JavaScript-data zoals airports, aircraft en frequencies later liever uit een database of configuratiebestand halen in plaats van hardcoded in JavaScript. Voor dit project was het voldoende, maar voor een grotere applicatie is dat minder flexibel.

## 13. Conclusie

Ik heb een werkende NAVLOG-applicatie gemaakt met PHP, MySQL, PDO en OOP. De applicatie ondersteunt flightbeheer, legbeheer, databaseopslag, validatie, weather data, fuel calculation, screenshots, een database-export en een nette README. Het project is geschikt om in te leveren en laat zien dat ik backend, database, OOP en frontend kan combineren in één werkende applicatie.