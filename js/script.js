/* =================================================
   NAVLOG FRONTEND SCRIPT
   Handles UI actions, dropdown logic, fuel calculation,
   guide steps, success messages and delete modals.
================================================= */

/* =================================================
   STATIC FRONTEND DATA
================================================= */

const airports = [
    {name: 'Kies veld', code: 'EH--', elevation: 0},
    {name: 'Rotterdam', code: 'EHRD', elevation: -14},
    {name: 'Midden-Zeeland', code: 'EHMZ', elevation: 6},
    {name: 'Seppe', code: 'EHSE', elevation: 30},
    {name: 'Schiphol', code: 'EHAM', elevation: -11},
    {name: 'Lelystad', code: 'EHLE', elevation: -12},
    {name: 'Eindhoven', code: 'EHEH', elevation: 74}
];

const aircrafts = [
    {callsign: 'Kies toestel', type: ''},
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
    {name: 'Kies veld', freq: ''},
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
    initializeSuccessMessageAutoHide();
});

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

    if (main) main.style.marginLeft = '0';
    if (nav) nav.style.display = 'none';

    setTimeout(() => {
        window.print();

        if (main) main.style.marginLeft = originalMargin;
        if (nav) nav.style.display = originalDisplay;
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

    if (overlay) overlay.style.display = 'none';
    if (tooltip) tooltip.style.display = 'none';

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
        deleteForm.action = 'index.php?flight_id=' + encodeURIComponent(flightId) + '#table2';
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