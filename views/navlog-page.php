<?php

$flights = $viewData['flights'];
$selectedFlight = $viewData['selectedFlight'];
$selectedLegs = $viewData['selectedLegs'];
$databaseLegRows = $viewData['databaseLegRows'];
$legArray = $viewData['legArray'];
$windData = $viewData['windData'];
$weatherIcaoCode = $viewData['weatherIcaoCode'];
$weatherMessage = $viewData['weatherMessage'];
$tafData = $viewData['tafData'];
$tafIcaoCode = $viewData['tafIcaoCode'];
$tafMessage = $viewData['tafMessage'];
$errorMessage = $viewData['errorMessage'];
$successMessage = $viewData['successMessage'];
$validationErrors = $viewData['validationErrors'];
$fieldErrors = $viewData['fieldErrors'];
$submittedNavlogRows = $viewData['submittedNavlogRows'];

$successCode = $viewData['successCode'];
$viewHelpers = NavlogViewDataBuilder::build(
    $selectedFlight,
    $flights,
    $selectedLegs,
    $databaseLegRows,
    $legArray,
    $fieldErrors,
    $postData,
    $windData,
    $weatherIcaoCode,
    $weatherMessage,
    $tafData,
    $tafIcaoCode,
    $tafMessage,
    $errorMessage,
    $successMessage,
    $successCode,
    $validationErrors
);

$aircraftView = $viewHelpers['aircraftView'];
$flightFormView = $viewHelpers['flightFormView'];
$weatherPanelView = $viewHelpers['weatherPanelView'];
$tafPanelView = $viewHelpers['tafPanelView'];
$deleteModalView = $viewHelpers['deleteModalView'];
$flightOptions = $viewHelpers['flightOptions'];


$selectedFlightLabel = $viewHelpers['selectedFlightLabel'];
$selectedFlightView = $viewHelpers['selectedFlightView'];
$loadedLegCount = $viewHelpers['loadedLegCount'];
$graphicalLegSection = $viewHelpers['graphicalLegSection'];
$navlogTableHeaderGroups = $viewHelpers['navlogTableHeaderGroups'];
$navlogTableHeaderColumns = $viewHelpers['navlogTableHeaderColumns'];
$navlogTableRows = $viewHelpers['navlogTableRows'];

$navigationItems = $viewHelpers['navigationItems'];
$footerItems = $viewHelpers['footerItems'];
$guideControls = $viewHelpers['guideControls'];
$pageHeader = $viewHelpers['pageHeader'];
$feedbackView = $viewHelpers['feedbackView'];
$feedbackVisibility = $viewHelpers['feedbackVisibility'];
$pageVisibility = $viewHelpers['pageVisibility'];
$weatherVisibility = $viewHelpers['weatherVisibility'];
$formActions = $viewHelpers['formActions'];
$measuringPointFields = $viewHelpers['measuringPointFields'];
$measuringPointResults = $viewHelpers['measuringPointResults'];
$measuringPointTableHeaders = $viewHelpers['measuringPointTableHeaders'];
$fuelCalculationFields = $viewHelpers['fuelCalculationFields'];
$fuelCalculationResultLabels = $viewHelpers['fuelCalculationResultLabels'];
$databaseErrorPanel = $feedbackView['databaseErrorPanel'];
$generalErrorPanel = $feedbackView['generalErrorPanel'];
$updateFlightErrorPanel = $feedbackView['updateFlightErrorPanel'];
$addFlightErrorPanel = $feedbackView['addFlightErrorPanel'];
$aircraftTimingErrorPanel = $feedbackView['aircraftTimingErrorPanel'];
$navlogTableErrorPanel = $feedbackView['navlogTableErrorPanel'];
$generalSuccessPanel = $feedbackView['generalSuccessPanel'];
$manageFlightSuccessPanel = $feedbackView['manageFlightSuccessPanel'];
$addFlightSuccessPanel = $feedbackView['addFlightSuccessPanel'];
$aircraftTimingSuccessPanel = $feedbackView['aircraftTimingSuccessPanel'];
$navlogTableSuccessPanel = $feedbackView['navlogTableSuccessPanel'];


?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>

    <title><?= ViewHelper::e($pageHeader['title']) ?></title>
</head>

<body class="contact">
<header class="masthead">

    <nav class="menu">
        <ul>
            <?= NavigationMenuRenderer::render($navigationItems) ?>
        </ul>
    </nav>
</header>

<article class="main">
    <header class="title">
        <h1><?= ViewHelper::e($pageHeader['heading']) ?></h1>
    </header>

    <?= PageFeedbackRenderer::renderTopFeedback(
        $feedbackVisibility,
        $databaseErrorPanel,
        $generalErrorPanel,
        $generalSuccessPanel
    ) ?>

    <form id="load-flight-panel" method="get" action="index.php#table2" class="database-panel">
        <label for="flight_id"><strong>Load saved flight:</strong></label>
        <select id="flight_id" name="flight_id" onchange="this.form.submit()" class="flight-select" data-step="1" data-text="Select the flight you want to prepare. The aircraft details, timing data and NAVLOG legs will load for this flight.">
            <?= FlightSelectRenderer::renderOptions($flightOptions) ?>
        </select>
        <span class="panel-inline-info">Loaded legs: <?= (int)$loadedLegCount ?></span>
    </form>
    <?php if ($pageVisibility['hasSelectedFlight']): ?>
        <details id="manage-flight-panel" class="database-panel manage-flight-panel" <?= $feedbackVisibility['shouldOpenManageFlight'] ? 'open' : '' ?>>
            <summary class="panel-summary">
                <span>Manage selected flight</span>
                <small><?= $selectedFlightLabel ?></small>
            </summary>

            <?= FlightPanelFeedbackRenderer::renderManageFlight(
                $feedbackVisibility,
                $updateFlightErrorPanel,
                $manageFlightSuccessPanel
            ) ?>

            <form method="post" action="<?= $formActions['manageFlight'] ?>" class="edit-flight-form" novalidate>
                <input type="hidden" name="action" value="update_flight">
                <input type="hidden" name="flight_id" value="<?= $selectedFlightView['id'] ?>">


                <div class="add-flight-grid">
                    <div class="add-flight-field">
                        <label>Date</label>
                        <input type="date" name="edit_date" value="<?= $flightFormView['edit']['date'] ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Departure</label>
                        <input type="text" name="edit_departure" value="<?= $flightFormView['edit']['departure'] ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Destination</label>
                        <input type="text" name="edit_destination" value="<?= $flightFormView['edit']['destination'] ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dept elev.</label>
                        <input type="text" name="edit_departure_elevation" value="<?= $flightFormView['edit']['departureElevation'] ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dest elev.</label>
                        <input type="text" name="edit_destination_elevation" value="<?= $flightFormView['edit']['destinationElevation'] ?>">
                    </div>

                    <div class="add-flight-field">
                        <label>Dept alt.</label>
                        <input type="number" name="edit_departure_altitude" value="<?= $flightFormView['edit']['departureAltitude'] ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>Dest alt.</label>
                        <input type="number" name="edit_destination_altitude" value="<?= $flightFormView['edit']['destinationAltitude'] ?>" required>
                    </div>

                    <div class="add-flight-field">
                        <label>TAS</label>
                        <input type="number" name="edit_tas" value="<?= $flightFormView['edit']['tas'] ?>" required>
                    </div>
                </div>

                <div class="manage-flight-action-row">
                    <button type="submit" class="add-flight-button manage-flight-button">Update flight</button>

                    <button type="button" class="delete-flight-button" onclick="openDeleteFlightModal()">
                        Delete flight
                    </button>
                </div>
            </form>

            <form id="delete-flight-form" method="post" class="delete-flight-form">
                <input type="hidden" name="action" value="delete_flight">
                <input type="hidden" name="flight_id" value="<?= $selectedFlightView['id'] ?>">
            </form>
        </details>
    <?php endif; ?>
    <?= WeatherPanelRenderer::renderMetar($weatherPanelView, $weatherVisibility, $formActions) ?>

    <details id="add-flight-panel" class="add-flight-panel collapsible-panel" <?= $feedbackVisibility['shouldOpenAddFlight'] ? 'open' : '' ?> data-step="2" data-text="Create a new flight when you need a new route. After saving, you can complete the aircraft data and NAVLOG for that flight.">
        <summary class="panel-summary">
            <span>Add new flight</span>
        </summary>

        <?= FlightPanelFeedbackRenderer::renderAddFlight(
            $feedbackVisibility,
            $addFlightErrorPanel,
            $addFlightSuccessPanel
        ) ?>

        <form method="post" action="<?= $formActions['addFlight'] ?>" novalidate>
            <input type="hidden" name="action" value="add_flight">

            <div class="add-flight-grid">
                <div class="add-flight-field">
                    <label>Date</label>
                    <input type="date" name="date" value="<?= $flightFormView['add']['date'] ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Departure</label>
                    <input type="text" name="departure" value="<?= $flightFormView['add']['departure'] ?>" placeholder="EHRD" required>
                </div>

                <div class="add-flight-field">
                    <label>Destination</label>
                    <input type="text" name="destination" value="<?= $flightFormView['add']['destination'] ?>" placeholder="EHAM" required>
                </div>

                <div class="add-flight-field">
                    <label>Dept elev.</label>
                    <input type="text" name="departure_elevation" value="<?= $flightFormView['add']['departureElevation'] ?>" placeholder="-14">
                </div>

                <div class="add-flight-field">
                    <label>Dest elev.</label>
                    <input type="text" name="destination_elevation" value="<?= $flightFormView['add']['destinationElevation'] ?>" placeholder="-11">
                </div>

                <div class="add-flight-field">
                    <label>Dept alt.</label>
                    <input type="number" name="departure_altitude" value="<?= $flightFormView['add']['departureAltitude'] ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>Dest alt.</label>
                    <input type="number" name="destination_altitude" value="<?= $flightFormView['add']['destinationAltitude'] ?>" required>
                </div>

                <div class="add-flight-field">
                    <label>TAS</label>
                    <input type="number" name="tas" value="<?= $flightFormView['add']['tas'] ?>" required>
                </div>
            </div>

            <button type="submit" class="add-flight-button">Save flight</button>
        </form>
    </details>

    <details id="fuel-calculation-panel" class="database-panel collapsible-panel" data-step="5" data-text="Use the fuel calculation to check whether the selected flight has enough fuel for taxi, trip, reserve and extra fuel.">
        <summary class="panel-summary">
            <span>Fuel calculation</span>
            <small>
                Selected flight: <?= $selectedFlightLabel ?>
            </small>
        </summary>

        <div class="fuel-grid">
            <?= FuelCalculationRenderer::renderFields($fuelCalculationFields) ?>
        </div>

        <button type="button" class="add-flight-button" onclick="calculateFuel()">Calculate fuel</button>

        <div class="fuel-result">
            <?= FuelCalculationRenderer::renderResult($fuelCalculationResultLabels) ?>
        </div>
    </details>

    <?= WeatherPanelRenderer::renderTaf($tafPanelView, $weatherVisibility, $formActions) ?>

    <?php if ($pageVisibility['hasNoSelectedFlight']): ?>
        <?= NoFlightPanelRenderer::render() ?>
    <?php else: ?>
        <form id="aircraft-timing-table-form" method="post" action="<?= $formActions['aircraftTiming'] ?>">
            <input type="hidden" name="action" value="save_aircraft_timing">
            <input type="hidden" name="flight_id" value="<?= $selectedFlightView['id'] ?>">
        </form>

        <?= FeedbackSectionRenderer::renderAircraftTiming($feedbackVisibility, $aircraftTimingErrorPanel, $aircraftTimingSuccessPanel) ?>

        <?= AircraftTimingTableRenderer::render($aircraftView) ?>

        <div class="aircraft-table-actions">
            <button type="submit" form="aircraft-timing-table-form" class="add-flight-button">Save aircraft timing</button>
        </div>

        <form id="navlog-table-form" method="post" action="<?= $formActions['navlogTable'] ?>">
            <input type="hidden" name="action" value="save_navlog_table">
            <input type="hidden" name="flight_id" value="<?= $selectedFlightView['id'] ?>">
        </form>

        <?= FeedbackSectionRenderer::renderNavlogTable($feedbackVisibility, $navlogTableErrorPanel, $navlogTableSuccessPanel) ?>

    <table id="table2" data-tas="<?= $selectedFlightView['tas'] ?>" data-step="4" data-text="Complete the blue NAVLOG fields directly in the table. The red fields calculate headings, ground speed, time and distance automatically.">
        <?= NavlogTableHeaderRenderer::renderGroupRow($navlogTableHeaderGroups) ?>
        <?= NavlogTableHeaderRenderer::renderColumnRow($navlogTableHeaderColumns) ?>
        <?= NavlogTableRowRenderer::renderRows($navlogTableRows, $selectedFlightView) ?>
    </table>

    <?= AlternateTableRenderer::render() ?>

        <div class="navlog-table-actions">
            <button type="submit" form="navlog-table-form" class="add-flight-button">Save legs</button>
        </div>
    <?php endif; ?>

    <?php if ($pageVisibility['showGraphicalLegView']): ?>
        <section id="graphical-leg-view" class="graphical-leg-panel" data-step="8" data-text="Use the graphical leg view to visualize the first loaded leg of the selected flight.">
            <div class="graphical-leg-header">
                <div>
                    <h2>Graphical leg view</h2>
                    <p>Visual helper based on the first NAVLOG leg of the selected flight.</p>
                </div>
                <div class="graphical-leg-actions">
                    <span><?= $selectedFlightLabel ?></span>
                    <button type="button" class="correction-toggle-button" id="correction_toggle_button" aria-expanded="false" data-step="9" data-text="Use the 1:60 correction helper to estimate off-track distance, closing angle and course correction during navigation preparation.">
                        1:60 correction
                    </button>
                </div>
            </div>

            <div class="graphical-leg-grid">
                <div class="graphical-leg-card">
                    <h3>Route</h3>
                    <div class="route-line">
                        <span class="route-point route-start"><?= $graphicalLegSection['start'] ?></span>
                        <span class="route-segment"></span>
                        <span class="route-point route-checkpoint"><?= $graphicalLegSection['checkpoint'] ?></span>
                        <span class="route-segment"></span>
                        <span class="route-point route-end"><?= $graphicalLegSection['destination'] ?></span>
                    </div>
                </div>

                <div class="graphical-leg-card">
                    <h3>Selected leg data</h3>
                    <dl class="leg-data-list">
                        <?= GraphicalLegDetailsRenderer::render($graphicalLegSection['details']) ?>
                    </dl>
                </div>

                <div class="graphical-leg-card measuring-point-card" id="measuring_point_card" aria-hidden="true">
                    <h3>1:60 correction calculator</h3>
                    <p class="measuring-point-intro">
                        Estimate off-track distance, closing angle and course correction for the selected leg.
                    </p>

                    <div class="measuring-point-controls" data-total-distance="<?= $graphicalLegSection['distance'] ?>">
                        <div class="measuring-point-field">
                            <label for="track_error_input"><?= ViewHelper::e($measuringPointFields['trackErrorLabel']) ?></label>
                            <input id="track_error_input" type="text" value="3" inputmode="numeric" pattern="[0-9]*" maxlength="2">
                        </div>

                        <div class="measuring-point-field">
                            <label for="measuring_point_slider"><?= ViewHelper::e($measuringPointFields['measuringPointLabel']) ?></label>
                            <input id="measuring_point_slider" type="range" min="1" max="<?= $graphicalLegSection['distance'] ?>" value="1">
                        </div>
                    </div>

                    <div class="measuring-point-visual">
                        <span class="measuring-point-label">0 NM</span>
                        <div class="measuring-point-track">
                            <span id="measuring_point_marker" class="measuring-point-marker"></span>
                        </div>
                        <span class="measuring-point-label"><?= $graphicalLegSection['distance'] ?> NM</span>
                    </div>

                    <div class="measuring-point-results">
                        <?= MeasuringPointRenderer::renderResults($measuringPointResults) ?>
                    </div>

                    <table class="measuring-point-table">
                        <thead>
                        <tr>
                            <?= MeasuringPointRenderer::renderTableHeaders($measuringPointTableHeaders) ?>
                        </tr>
                        </thead>
                        <tbody id="measuring_point_table_body">
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <footer>
        <?= FooterMenuRenderer::render($footerItems) ?>
    </footer>
</article>

<?= DeleteModalRenderer::renderFlightModal($deleteModalView) ?>
<?= DeleteModalRenderer::renderLegModal($deleteModalView) ?>

<div id="guide-overlay"></div>
<div id="guide-tooltip">
    <div id="guide-text"></div>
    <div id="guide-controls">
        <?= GuideOverlayRenderer::renderControls($guideControls) ?>
    </div>
</div>
</body>
</html>