/* =================================================
   NAVLOG FRONTEND SCRIPT
   Handles table calculations, dropdowns, fuel checks,
   guide steps, messages and delete modals.
================================================= */

/* =================================================
   STATIC FRONTEND DATA
================================================= */

const airports = [
    {name: 'Select airport', code: 'EH--', elevation: 0},
    {name: 'Rotterdam', code: 'EHRD', elevation: -14},
    {name: 'Midden-Zeeland', code: 'EHMZ', elevation: 6},
    {name: 'Seppe', code: 'EHSE', elevation: 30},
    {name: 'Schiphol', code: 'EHAM', elevation: -11},
    {name: 'Lelystad', code: 'EHLE', elevation: -12},
    {name: 'Eindhoven', code: 'EHEH', elevation: 74}
];

const aircrafts = [
    {callsign: 'Select aircraft', type: ''},
    {callsign: 'PH-HLR', type: 'DR-400'},
    {callsign: 'PH-NSC', type: 'DR-400'},
    {callsign: 'PH-SPZ', type: 'DR-400'},
    {callsign: 'PH-SVT', type: 'DR-400'},
    {callsign: 'PH-SVU', type: 'DR-400'},
    {callsign: 'PH-XYZ', type: 'DR-401'},
    {callsign: 'PH-SVP', type: 'Piper PA28'},
    {callsign: 'PH-VSY', type: 'Piper PA28'},
    {callsign: 'PH-SVN', type: 'R2000'}
];

const frequencies = [
    {name: 'Select frequency', freq: ''},
    {name: 'Rotterdam Tower', freq: '118.205'},
    {name: 'Midden-Zeeland Radio', freq: '119.255'},
    {name: 'Seppe Tower', freq: '120.655'},
    {name: 'Schiphol Tower', freq: '118.105'},
    {name: 'Lelystad Tower', freq: '135.180'},
    {name: 'Eindhoven Tower', freq: '131.005'},
    {name: '____________', freq: '______'},
    {name: 'Dutch Mil Info', freq: '132.350'},
    {name: 'Amsterdam Info', freq: '124.300'}
];

const alternateAirports = {
    'Rotterdam Airport': '118.205',
    'Seppe': '120.655',
    'Midden-Zeeland': '119.255',
    'Schiphol': '118.105',
    'Lelystad': '135.180',
    'Eindhoven': '131.005'
};

/* =================================================
   PAGE INITIALIZATION
================================================= */

document.addEventListener('DOMContentLoaded', function () {
    initializeAirportDropdowns();
    initializeAircraftDropdowns();
    initializeFrequencyDropdowns();
    initializeAlternateAirportDropdown();
    initializeMeasuringPointCalculator();
    initializeCorrectionToggle();
    initializeCorrectionMenuLink();
    initializeNavlogTableCalculations();
    initializeSuccessMessageAutoHide();
});

/* =================================================
   NAVLOG TABLE CALCULATIONS
   Updates calculated red cells when the user edits
   input values in the blue NAVLOG table.
================================================= */

function initializeNavlogTableCalculations() {
    const navlogTable = document.getElementById('table2');

    if (!navlogTable) {
        return;
    }

    const calculatedFields = ['variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'];

    navlogTable.addEventListener('input', function (event) {
        const input = event.target;

        if (!input.matches('.navlog-input')) {
            return;
        }

        if (!calculatedFields.includes(input.dataset.field)) {
            return;
        }

        updateNavlogTableCalculations(navlogTable);
    });

    updateNavlogTableCalculations(navlogTable);
}

function updateNavlogTableCalculations(navlogTable) {
    const rows = Array.from(navlogTable.querySelectorAll('tbody tr'));
    let accumulatedTime = 0;
    let accumulatedDistance = 0;

    rows.forEach(row => {
        const variation = getNavlogRowNumber(row, 'variation');
        const windDirection = getNavlogRowNumber(row, 'wind_dir');
        const windVelocity = getNavlogRowNumber(row, 'wind_v');
        const trueTrack = getNavlogRowNumber(row, 'tt');
        const distanceInterval = getNavlogRowNumber(row, 'dist_int');
        const tas = getNavlogTas(navlogTable);

        if (!hasNavlogCalculationInput(row)) {
            setNavlogCalculatedValue(row, 1, '');
            setNavlogCalculatedValue(row, 2, '');
            setNavlogCalculatedValue(row, 10, '');
            setNavlogCalculatedValue(row, 12, '');
            setNavlogCalculatedValue(row, 13, '');
            setNavlogCalculatedValue(row, 18, '');
            setNavlogCalculatedValue(row, 19, '');
            return;
        }

        const wca = calculateHeadingWca(windDirection, trueTrack, windVelocity, tas);
        const trueHeading = normalizeDegrees(trueTrack + wca);
        const magneticHeading = normalizeDegrees(trueHeading - variation);
        const groundSpeed = calculateGroundSpeed(windDirection, windVelocity, trueTrack, tas);
        const timeInterval = groundSpeed > 0 && distanceInterval > 0
            ? Math.round((distanceInterval / groundSpeed) * 60)
            : 0;

        accumulatedTime += timeInterval;
        accumulatedDistance += distanceInterval;

        setNavlogCalculatedValue(row, 1, accumulatedTime);
        setNavlogCalculatedValue(row, 2, timeInterval);
        setNavlogCalculatedValue(row, 10, magneticHeading);
        setNavlogCalculatedValue(row, 12, trueHeading);
        setNavlogCalculatedValue(row, 13, wca);
        setNavlogCalculatedValue(row, 18, accumulatedDistance);
        setNavlogCalculatedValue(row, 19, groundSpeed);
    });
}

function getNavlogRowNumber(row, fieldName) {
    const input = row.querySelector(`[data-field="${fieldName}"]`);
    const value = input ? parseFloat(input.value) : 0;

    return Number.isFinite(value) ? value : 0;
}

function hasNavlogCalculationInput(row) {
    return ['variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'].some(fieldName => {
        const input = row.querySelector(`[data-field="${fieldName}"]`);
        return input && input.value.trim() !== '';
    });
}

function setNavlogCalculatedValue(row, cellIndex, value) {
    const cell = row.cells[cellIndex];
    const input = cell ? cell.querySelector('input') : null;

    if (input) {
        input.value = value;
    }
}

function getNavlogTas(navlogTable) {
    const tableTas = navlogTable ? parseFloat(navlogTable.dataset.tas) : NaN;

    if (Number.isFinite(tableTas) && tableTas > 0) {
        return tableTas;
    }

    const tasInput = document.querySelector('[name="tas"], [name="TAS"], #tas, #TAS');
    const inputTas = tasInput ? parseFloat(tasInput.value) : NaN;

    return Number.isFinite(inputTas) && inputTas > 0 ? inputTas : 105;
}

function calculateHeadingWca(windDirection, trueTrack, windVelocity, tas) {
    const safeTas = Math.max(1, tas);
    const angle = toRadians(trueTrack - (windDirection - 180));
    const ratio = clamp((windVelocity * Math.sin(angle)) / safeTas, -1, 1);

    return Math.round(toDegrees(Math.asin(ratio)));
}

function calculateGroundSpeed(windDirection, windVelocity, trueTrack, tas) {
    const windAngle = toRadians(windDirection - trueTrack);
    const groundSpeed = tas - (windVelocity * Math.cos(windAngle));

    return Math.max(0, Math.round(groundSpeed));
}

function normalizeDegrees(degrees) {
    let normalized = degrees % 360;

    if (normalized < 0) {
        normalized += 360;
    }

    return Math.round(normalized);
}

function toRadians(degrees) {
    return degrees * Math.PI / 180;
}

function toDegrees(radians) {
    return radians * 180 / Math.PI;
}

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

/* =================================================
   LIGHT MODE
================================================= */

function toggleAchtergrond(event) {
    event.preventDefault();
    document.body.classList.toggle('light-mode');
}

/* =================================================
   PRINT
================================================= */

function printPagina() {
    const main = document.querySelector('.main');
    const nav = document.querySelector('nav.menu');
    const originalMargin = main ? main.style.marginLeft : '';
    const originalDisplay = nav ? nav.style.display : '';

    if (main) {
        main.style.marginLeft = '0';
    }

    if (nav) {
        nav.style.display = 'none';
    }

    setTimeout(() => {
        window.print();

        if (main) {
            main.style.marginLeft = originalMargin;
        }

        if (nav) {
            nav.style.display = originalDisplay;
        }
    }, 100);
}

/* =================================================
   AIRPORT DROPDOWNS
================================================= */

function initializeAirportDropdowns() {
    const airportSelects = document.querySelectorAll('.airportSelect');
    const elevationInputs = document.querySelectorAll('.elevationInput');

    airportSelects.forEach((select, index) => {
        if (select.options.length === 0) {
            airports.forEach(airport => {
                const option = document.createElement('option');
                option.value = String(airport.elevation);
                option.textContent = airport.name;
                option.dataset.code = airport.code;
                option.dataset.label = airport.name;
                select.appendChild(option);
            });
        }

        if (elevationInputs[index] && !elevationInputs[index].value && select.options.length > 0) {
            elevationInputs[index].value = select.options[0].value;
        }

        select.addEventListener('change', function () {
            Array.from(this.options).forEach(option => {
                if (option.dataset.label) {
                    option.textContent = option.dataset.label;
                }
            });

            const selected = this.options[this.selectedIndex];

            if (!selected) {
                return;
            }

            if (selected.dataset.code) {
                selected.textContent = selected.dataset.code;
            }

            if (elevationInputs[index]) {
                elevationInputs[index].value = selected.value;
            }
        });
    });
}

/* =================================================
   AIRCRAFT DROPDOWNS
================================================= */

function initializeAircraftDropdowns() {
    const aircraftSelects = document.querySelectorAll('.aircraftSelect');

    aircraftSelects.forEach(select => {
        const selectedRegistration = select.value || select.options[select.selectedIndex]?.textContent || '';
        const hasRealOptions = Array.from(select.options).some(option => option.value && option.value !== '');

        if (!hasRealOptions && !select.disabled) {
            aircrafts.forEach(aircraft => {
                const option = document.createElement('option');
                option.value = aircraft.callsign;
                option.textContent = aircraft.callsign;
                option.dataset.label = aircraft.callsign;
                option.dataset.type = aircraft.type;
                select.appendChild(option);
            });
        }

        Array.from(select.options).forEach(option => {
            const aircraft = aircrafts.find(item => item.callsign === option.value || item.callsign === option.textContent);

            if (aircraft) {
                option.dataset.type = aircraft.type;
                option.dataset.label = aircraft.callsign;
                option.value = aircraft.callsign;
                option.textContent = aircraft.callsign;
            }
        });

        if (selectedRegistration) {
            select.value = selectedRegistration;
        }

        const typeInput = findRelatedTypeInput(select);

        const updateAircraftType = () => {
            const selected = select.options[select.selectedIndex];

            if (!typeInput || !selected) {
                return;
            }

            const registration = selected.value || selected.textContent;
            const aircraft = aircrafts.find(item => item.callsign === registration);
            typeInput.value = aircraft ? aircraft.type : '';
        };

        if (typeInput && !typeInput.value) {
            updateAircraftType();
        }

        if (!select.disabled) {
            select.addEventListener('change', updateAircraftType);
        }
    });
}

function findRelatedTypeInput(select) {
    const form = select.closest('form');
    const table = select.closest('table');

    if (form) {
        return form.querySelector('.typeInput');
    }

    if (table) {
        return table.querySelector('.typeInput');
    }

    return null;
}

/* =================================================
   FREQUENCY DROPDOWNS
================================================= */

function initializeFrequencyDropdowns() {
    const frequencySelects = document.querySelectorAll('.freqSelect');

    frequencySelects.forEach(select => {
        if (select.options.length === 0) {
            frequencies.forEach(entry => {
                const option = document.createElement('option');
                option.value = entry.freq;
                option.textContent = entry.name;
                option.dataset.label = entry.name;
                select.appendChild(option);
            });
        }

        select.addEventListener('change', function () {
            Array.from(this.options).forEach(option => {
                if (option.dataset.label) {
                    option.textContent = option.dataset.label;
                }
            });

            const selected = this.options[this.selectedIndex];

            if (selected) {
                selected.textContent = selected.value;
            }
        });
    });
}

function initializeAlternateAirportDropdown() {
    const alternateSelect = document.getElementById('airportSelect');
    const radioInput = document.getElementById('radioInput');

    if (!alternateSelect || !radioInput) {
        return;
    }

    Object.entries(alternateAirports).forEach(([name]) => {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        alternateSelect.appendChild(option);
    });

    alternateSelect.addEventListener('change', function () {
        radioInput.value = alternateAirports[this.value] || '';
    });
}

/* =================================================
   FUEL CALCULATION
================================================= */

function getFuelNumber(id) {
    const field = document.getElementById(id);
    const value = field ? parseFloat(field.value) : 0;

    return Number.isFinite(value) ? value : 0;
}

function calculateFuel() {
    const totalRequiredFuelOutput = document.getElementById('total_required_fuel');
    const remainingFuelOutput = document.getElementById('remaining_fuel');
    const fuelStatusOutput = document.getElementById('fuel_status');

    if (!totalRequiredFuelOutput || !remainingFuelOutput || !fuelStatusOutput) {
        return;
    }

    const fuelOnBoard = getFuelNumber('fuel_on_board');
    const taxiFuel = getFuelNumber('taxi_fuel');
    const tripFuel = getFuelNumber('trip_fuel');
    const reserveFuel = getFuelNumber('reserve_fuel');
    const extraFuel = getFuelNumber('extra_fuel');
    const finalReserveFuel = getFuelNumber('final_reserve_fuel');

    const totalRequiredFuel = taxiFuel + tripFuel + reserveFuel + extraFuel + finalReserveFuel;
    const remainingFuel = fuelOnBoard - totalRequiredFuel;
    const fuelStatus = remainingFuel >= 0 ? 'Enough fuel' : 'Not enough fuel';

    totalRequiredFuelOutput.textContent = totalRequiredFuel.toFixed(1);
    remainingFuelOutput.textContent = remainingFuel.toFixed(1);
    fuelStatusOutput.textContent = fuelStatus;
}

/* =================================================
   1:60 CORRECTION CALCULATOR
   Updates the measuring point slider, route marker
   and correction values for the selected leg.
================================================= */

function initializeMeasuringPointCalculator() {
    const controls = document.querySelector('.measuring-point-controls');
    const slider = document.getElementById('measuring_point_slider');
    const trackErrorInput = document.getElementById('track_error_input');
    const marker = document.getElementById('measuring_point_marker');
    const selectedNmOutput = document.getElementById('selected_nm_value');
    const offTrackOutput = document.getElementById('off_track_value');
    const closingAngleOutput = document.getElementById('closing_angle_value');
    const courseCorrectionOutput = document.getElementById('course_correction_value');
    const tableBody = document.getElementById('measuring_point_table_body');

    if (!controls || !slider || !trackErrorInput || !marker || !selectedNmOutput || !offTrackOutput || !closingAngleOutput || !courseCorrectionOutput || !tableBody) {
        return;
    }

    // The maximum distance comes from the selected leg.
    const totalDistance = Math.max(1, parseInt(controls.dataset.totalDistance, 10) || 1);
    slider.max = String(totalDistance);

    const updateCalculator = () => {
        const distanceFlown = Math.max(1, parseInt(slider.value, 10) || 1);
        const trackError = getTrackErrorValue(trackErrorInput);

        // Off-track distance follows the selected NM and track error.
        const offTrack = distanceFlown * trackError;

        // Closing angle uses the same simple correction pattern.
        const closingAngle = distanceFlown * 2;

        // Course correction combines off-track distance and closing angle.
        const courseCorrection = offTrack + closingAngle;

        selectedNmOutput.textContent = String(distanceFlown);
        offTrackOutput.textContent = formatMeasuringPointNumber(offTrack);
        closingAngleOutput.textContent = formatMeasuringPointNumber(closingAngle);
        courseCorrectionOutput.textContent = formatMeasuringPointNumber(courseCorrection);

        updateMeasuringPointMarker(marker, distanceFlown, totalDistance);
        fillMeasuringPointTable(tableBody, totalDistance, trackError);
    };

    slider.addEventListener('input', updateCalculator);
    trackErrorInput.addEventListener('input', updateCalculator);

    updateCalculator();
}

function initializeCorrectionToggle() {
    const toggleButton = document.getElementById('correction_toggle_button');
    const measuringPointCard = document.getElementById('measuring_point_card');

    if (!toggleButton || !measuringPointCard) {
        return;
    }

    toggleButton.addEventListener('click', function () {
        const isVisible = measuringPointCard.classList.toggle('is-visible');

        // Keep accessibility state in sync with visibility.
        measuringPointCard.setAttribute('aria-hidden', String(!isVisible));
        toggleButton.setAttribute('aria-expanded', String(isVisible));
        toggleButton.textContent = isVisible ? 'Hide 1:60 correction' : '1:60 correction';
    });
}

function initializeCorrectionMenuLink() {
    const menuLink = document.getElementById('menu_correction_link');
    const toggleButton = document.getElementById('correction_toggle_button');
    const measuringPointCard = document.getElementById('measuring_point_card');

    if (!menuLink || !toggleButton || !measuringPointCard) {
        return;
    }

    menuLink.addEventListener('click', function () {
        // Open the 1:60 correction panel from the menu.
        if (!measuringPointCard.classList.contains('is-visible')) {
            measuringPointCard.classList.add('is-visible');
            measuringPointCard.setAttribute('aria-hidden', 'false');
            toggleButton.setAttribute('aria-expanded', 'true');
            toggleButton.textContent = 'Hide 1:60 correction';
        }
    });
}

function getTrackErrorValue(trackErrorInput) {
    trackErrorInput.value = trackErrorInput.value.replace(/\D/g, '').slice(0, 2);

    return Math.max(0, parseInt(trackErrorInput.value, 10) || 0);
}

function updateMeasuringPointMarker(marker, distanceFlown, totalDistance) {
    const percentage = totalDistance <= 1 ? 0 : ((distanceFlown - 1) / (totalDistance - 1)) * 100;
    marker.style.left = percentage + '%';
}

function fillMeasuringPointTable(tableBody, totalDistance, trackError) {
    tableBody.innerHTML = '';

    const visibleRows = Math.min(5, totalDistance);

    for (let nm = 1; nm <= visibleRows; nm++) {
        const row = document.createElement('tr');
        const offTrack = nm * trackError;
        const closingAngle = nm * 2;
        const courseCorrection = offTrack + closingAngle;

        row.innerHTML = `
            <td>${nm}</td>
            <td>${formatMeasuringPointNumber(offTrack)}</td>
            <td>${formatMeasuringPointNumber(closingAngle)}</td>
            <td>${formatMeasuringPointNumber(courseCorrection)}</td>
        `;

        tableBody.appendChild(row);
    }
}

function formatMeasuringPointNumber(value) {
    return Number.isInteger(value) ? String(value) : value.toFixed(1);
}

/* =================================================
   STEP GUIDE
================================================= */

let currentStep = 0;
let steps = [];

function startGuide() {
    steps = Array.from(document.querySelectorAll('[data-step]'))
        .sort((a, b) => Number(a.dataset.step) - Number(b.dataset.step));
    currentStep = 0;
    showStep();
}

function showStep() {
    const overlay = document.getElementById('guide-overlay');
    const tooltip = document.getElementById('guide-tooltip');
    const text = document.getElementById('guide-text');

    if (currentStep < 0 || currentStep >= steps.length || !overlay || !tooltip || !text) {
        return;
    }

    const element = steps[currentStep];
    const parentDetails = element.closest('details');

    if (parentDetails && !parentDetails.open) {
        parentDetails.open = true;
    }

    requestAnimationFrame(() => {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
        });

        const rect = element.getBoundingClientRect();

        steps.forEach(step => step.removeAttribute('data-highlight'));
        element.setAttribute('data-highlight', 'true');

        overlay.style.display = 'block';
        tooltip.style.display = 'block';
        text.textContent = element.dataset.text;

        tooltip.style.top = window.scrollY + rect.bottom + 10 + 'px';
        tooltip.style.left = rect.left + 'px';
    });
}

function nextStep(event) {
    if (event) {
        event.preventDefault();
    }

    if (currentStep < steps.length - 1) {
        currentStep++;
        showStep();
    } else {
        endGuide();
    }
}

function prevStep(event) {
    if (event) {
        event.preventDefault();
    }

    if (currentStep > 0) {
        currentStep--;
        showStep();
    }
}

function endGuide(event) {
    if (event) {
        event.preventDefault();
    }

    const overlay = document.getElementById('guide-overlay');
    const tooltip = document.getElementById('guide-tooltip');

    if (overlay) {
        overlay.style.display = 'none';
    }

    if (tooltip) {
        tooltip.style.display = 'none';
    }

    steps.forEach(step => step.removeAttribute('data-highlight'));
    steps = [];
    currentStep = 0;
}

/* =================================================
   SUCCESS MESSAGE
================================================= */

function initializeSuccessMessageAutoHide() {
    const successMessage = document.querySelector('.success-message');

    if (!successMessage) {
        return;
    }

    setTimeout(() => {
        successMessage.style.display = 'none';

        const url = new URL(window.location.href);
        url.searchParams.delete('success');
        window.history.replaceState({}, '', url.toString());
    }, 4000);
}

/* =================================================
   DELETE MODALS
================================================= */

function openDeleteFlightModal() {
    const modal = document.getElementById('delete-flight-modal');

    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeDeleteFlightModal() {
    const modal = document.getElementById('delete-flight-modal');

    if (modal) {
        modal.style.display = 'none';
    }
}

function submitDeleteFlightForm() {
    const deleteForm = document.getElementById('delete-flight-form');

    if (deleteForm) {
        deleteForm.submit();
    }
}

function openDeleteLegModal(flightId, legId) {
    const modal = document.getElementById('delete-leg-modal');
    const flightField = document.getElementById('delete_leg_flight_id');
    const legField = document.getElementById('delete_leg_id');
    const deleteForm = document.getElementById('delete-leg-form');

    if (flightField) {
        flightField.value = flightId;
    }

    if (legField) {
        legField.value = legId;
    }

    if (deleteForm) {
        deleteForm.action = `index.php?flight_id=${encodeURIComponent(flightId)}#table2`;
    }

    if (modal) {
        modal.style.display = 'flex';
    }
}

function closeDeleteLegModal() {
    const modal = document.getElementById('delete-leg-modal');

    if (modal) {
        modal.style.display = 'none';
    }
}