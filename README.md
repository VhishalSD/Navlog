

# NAVLOG School Project

This project is a PHP, MySQL, PDO and OOP NAVLOG application for managing flights and legs.

## Main functionality

- Create, select, edit and delete flights
- Add, edit and delete legs
- Store multiple legs in a flight plan
- Load saved flight plans from the database
- Show loaded legs in the NAVLOG table
- Use `Leg` and `LegArray` classes for OOP structure
- Save and load aircraft and timing data
- Show METAR/TAF weather data
- Use fuel calculation
- Show graphical leg view
- Use 1:60 correction calculator
- Print NAVLOG overview

## Technologies

- PHP
- MySQL
- PDO
- OOP
- HTML
- CSS
- JavaScript
- XAMPP

## Database

The database export is located in:

```text
database/navlog.sql
```

Import this file into MySQL before running the project.

## Local setup

1. Start Apache and MySQL in XAMPP.
2. Place the project folder inside the XAMPP `htdocs` folder.
3. Import `database/navlog.sql` into MySQL.
4. Check the database settings in `classes/Database.php`.
5. Open the project in the browser.

Example:

```text
http://localhost/Navlog-School/
```

## Screenshots

Screenshots are located in:

```text
screenshots/
```

Current screenshots:

```text
01-selected-flight.png
02-manage-flight.png
03-aircraft-timing.png
04-add-leg.png
05-edit-leg.png
06-navlog-table-leg-crud.png
07-delete-leg-modal.png
08-validation-example.png
09-weather-metar-taf.png
10-fuel-calculation.png
11-light-dark-mode.png
12-step-guide.png
13-print-view.png
14-graphical-leg-view.png
15-1-60-correction-calculator.png
```

## Author

Vishal Tewari