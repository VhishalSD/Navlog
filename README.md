# NAVLOG School Project

This project is a PHP, MySQL, PDO and OOP NAVLOG application for managing flight navigation logs.

The application allows a user to create and manage flights, add NAVLOG legs, store the data in a MySQL database and reload saved flight plans. The project is built as an OOP school project where the application logic, database access, validation, rendering and view-data preparation are separated into classes.

## Main functionality

- Create, select, edit and delete flights
- Add, edit and delete legs
- Store multiple legs under one flight plan
- Load saved flight plans from the database
- Show loaded legs in the NAVLOG table
- Use `Leg` and `LegArray` classes for the NAVLOG OOP structure
- Save and load aircraft and timing data
- Show METAR wind data
- Show TAF forecast data
- Use a fuel calculation panel
- Show a graphical leg overview
- Use a 1:60 correction calculator
- Open delete confirmation modals for flights and legs
- Use light/dark mode
- Use a step guide
- Print the NAVLOG overview

## Technologies

- PHP
- MySQL
- PDO
- Object-Oriented Programming
- HTML
- CSS
- JavaScript
- XAMPP

## OOP structure

The project is divided into clear class folders:

```text
classes/
├── Application/
├── Builders/
├── Controllers/
├── Database/
├── Helpers/
├── Models/
├── Renderers/
├── Services/
└── Autoloader.php
```

The view template is separated from the application bootstrap:

```text
views/
└── navlog-page.php
```

The `index.php` file is kept as a small bootstrap file:

```php
<?php
require_once __DIR__ . '/classes/Autoloader.php';

Autoloader::register(__DIR__);
echo NavlogApplication::create()->render();
```

## Class responsibilities

### Application

The `Application` folder contains the main application flow.

Examples:

- `NavlogApplication.php`
- `RequestHandler.php`
- `PageRequestView.php`

These classes start the application, handle the request data and prepare the page response.

### Controllers

The `Controllers` folder contains classes that process user actions.

Examples:

- `FlightController.php`
- `NavlogController.php`
- `AircraftTimingController.php`
- `WeatherController.php`

These classes handle actions such as saving flights, saving legs, deleting records and loading weather data.

### Models

The `Models` folder contains the main OOP data classes.

Examples:

- `Leg.php`
- `LegArray.php`

`Leg` represents one NAVLOG leg. `LegArray` stores and manages multiple `Leg` objects.

### Database

The `Database` folder contains the PDO database connection class.

Example:

- `Database.php`

This class is responsible for connecting the application to the MySQL database.

### Builders

The `Builders` folder contains classes that prepare data for the view.

Examples:

- `NavlogViewDataBuilder.php`
- `NavlogTableViewBuilder.php`
- `FlightFormViewBuilder.php`
- `WeatherPanelViewBuilder.php`

These classes prepare arrays and values that are later rendered in the view.

### Renderers

The `Renderers` folder contains classes that render reusable HTML sections.

Examples:

- `NavlogTableRowRenderer.php`
- `WeatherPanelRenderer.php`
- `FuelCalculationRenderer.php`
- `DeleteModalRenderer.php`

These classes keep the main view cleaner and prevent large HTML/PHP blocks inside `index.php`.

### Helpers

The `Helpers` folder contains small reusable helper classes.

Examples:

- `ViewHelper.php`
- `ValidationHelper.php`
- `FeedbackHelper.php`
- `FormActionHelper.php`

These classes help with escaping output, validation, feedback visibility and form actions.

### Services

The `Services` folder contains service classes such as weather scraping.

Example:

- `WeatherScraper.php`

## Database

The database export is located in:

```text
database/navlog.sql
```

Import this file into MySQL before running the project.

The application uses PDO for the database connection.

## Local setup

1. Start Apache and MySQL in XAMPP.
2. Place the project folder inside the XAMPP `htdocs` folder.
3. Import `database/navlog.sql` into MySQL.
4. Check the database settings in `classes/Database/Database.php`.
5. Open the project in the browser.

Example:

```text
http://localhost/Navlog-School/
```

## School criteria covered

This project covers the required school criteria:

- PDO class for the connection with the MySQL database
- Project is built with an OOP structure
- Application logic is placed inside classes
- The given GUI is implemented
- Legs can be saved
- A NAVLOG flight plan can be saved with multiple underlying legs

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
04-add-edit-leg-crud.png
05-delete-leg-modal.png
06-validation-example.png
07-weather-metar-taf.png
08-fuel-calculation.png
09-light-dark-mode.png
10-step-guide.png
11-print-view.png
```

## Author

Vishal Tewari