function toggleAchtergrond(event) {
    event.preventDefault();

    // Toggle a real light mode class instead of changing inline styles.
    document.body.classList.toggle('light-mode');
}

function PutThroughLegInfo(legID) {
    const nextLegID = legID + 1;
    const currentLegField = document.getElementById('leg' + legID + 'Name');
    const nextLegField = document.getElementById('leg' + nextLegID + 'Name');
    const iframe = document.getElementById('1_60');

    if (!currentLegField || !nextLegField || !iframe || !iframe.contentWindow) {
        return;
    }

    const currentLegName = currentLegField.value;
    const nextLegName = nextLegField.value;
    const iframeDocument = iframe.contentWindow.document;

    if (!iframeDocument) {
        return;
    }

    const naamA = iframeDocument.getElementById('naamA');
    const naamC = iframeDocument.getElementById('naamC');
    const afstandA = iframeDocument.getElementById('afstandA');
    const meetpuntC = iframeDocument.getElementById('meetpuntC');

    if (naamA) naamA.value = currentLegName;
    if (naamC) naamC.value = nextLegName;
    if (afstandA) afstandA.innerHTML = currentLegName;
    if (meetpuntC) meetpuntC.innerHTML = nextLegName;
}

function verwerkInvoer(value) {
    // Temporary input processing.
    return {
        auto1: value,
        auto2: value.length
    };
}

function printPagina() {
    const main = document.querySelector('.main');
    const nav = document.querySelector('nav.menu');

    // Store the original values so they can be restored after printing.
    const originalMargin = main ? main.style.marginLeft : '';
    const originalDisplay = nav ? nav.style.display : '';

    // Temporarily adjust the layout for printing.
    if (main) main.style.marginLeft = '0';
    if (nav) nav.style.display = 'none';

    // Wait briefly before starting the print dialog.
    setTimeout(() => {
        window.print();

        // Restore the original layout after printing.
        if (main) main.style.marginLeft = originalMargin;
        if (nav) nav.style.display = originalDisplay;
    }, 100);
}

// Select all input fields that should react to the Enter key.
const triggerFields = document.querySelectorAll('.trigger-veld');

triggerFields.forEach(field => {
    field.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            const value = this.value;
            const result = verwerkInvoer(value);
            const trueHeadingField = document.getElementById('1_TH');
            const windCorrectionField = document.getElementById('1_WCA');

            if (trueHeadingField) trueHeadingField.value = result.auto1;
            if (windCorrectionField) windCorrectionField.value = result.auto2;
        }
    });
});

// Airport data.
const airports = [
    {name: 'Kies veld', code: 'EH--', elevation: 0},
    {name: 'Rotterdam', code: 'EHRD', elevation: -14},
    {name: 'Midden-Zeeland', code: 'EHMZ', elevation: 6},
    {name: 'Seppe', code: 'EHSE', elevation: 30},
    {name: 'Schiphol', code: 'EHAM', elevation: -11},
    {name: 'Lelystad', code: 'EHLE', elevation: -12},
    {name: 'Eindhoven', code: 'EHEH', elevation: 74}
];

const airportSelects = document.querySelectorAll('.airportSelect');
const elevationInputs = document.querySelectorAll('.elevationInput');

airportSelects.forEach((select, index) => {
    // Fill the select field with the original labels.
    airports.forEach(airport => {
        const option = document.createElement('option');
        option.value = airport.elevation;
        option.textContent = airport.name;
        option.dataset.code = airport.code;
        option.dataset.label = airport.name;
        select.appendChild(option);
    });

    // Keep database values visible when a saved flight is loaded.
    if (elevationInputs[index] && !elevationInputs[index].value) {
        elevationInputs[index].value = select.options[0].value;
    }

    // Handle selection changes.
    select.addEventListener('change', function () {
        // Reset all options to their original names.
        Array.from(this.options).forEach(option => {
            option.textContent = option.dataset.label;
        });

        // Change the selected option to the ICAO code.
        const selected = this.options[this.selectedIndex];
        selected.textContent = selected.dataset.code;

        // Show the elevation in the input field.
        if (elevationInputs[index]) {
            elevationInputs[index].value = selected.value;
        }
    });
});

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

document.addEventListener('DOMContentLoaded', function () {
    const aircraftSelects = document.querySelectorAll('.aircraftSelect');
    const typeInputs = document.querySelectorAll('.typeInput');

    aircraftSelects.forEach((select, index) => {
        // Fill the dropdown with aircraft data.
        aircrafts.forEach(aircraft => {
            const option = document.createElement('option');
            option.value = aircraft.type;
            option.textContent = aircraft.callsign;
            option.dataset.label = aircraft.callsign;
            option.dataset.type = aircraft.type;
            select.appendChild(option);
        });

        // Set the first value immediately.
        if (typeInputs[index]) {
            typeInputs[index].value = select.options[0].dataset.type;
        }

        // Handle selection changes.
        select.addEventListener('change', function () {
            Array.from(this.options).forEach(option => {
                option.textContent = option.dataset.label;
            });

            const selected = this.options[this.selectedIndex];

            if (typeInputs[index]) {
                typeInputs[index].value = selected.dataset.type;
            }
        });
    });
});

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

document.addEventListener('DOMContentLoaded', function () {
    const frequencySelects = document.querySelectorAll('.freqSelect');

    frequencySelects.forEach(select => {
        // Fill every select field with the same frequency data.
        frequencies.forEach(entry => {
            const option = document.createElement('option');
            option.value = entry.freq;
            option.textContent = entry.name;
            option.dataset.label = entry.name;
            select.appendChild(option);
        });

        // Handle selection changes.
        select.addEventListener('change', function () {
            // Reset all option labels.
            Array.from(this.options).forEach(option => {
                option.textContent = option.dataset.label;
            });

            // Change the selected option text to the frequency.
            const selected = this.options[this.selectedIndex];
            selected.textContent = selected.value;
        });
    });
});

const alternateAirports = {
    'Rotterdam Airport': '118.205',
    'Seppe': '120.655',
    'Midden-Zeeland': '119.255',
    'Schiphol': '118.105',
    'Lelystad': '135.180',
    'Eindhoven': '131.005'
};

const alternateSelect = document.getElementById('airportSelect');
const radioInput = document.getElementById('radioInput');

if (alternateSelect && radioInput) {
    // Fill the select field.
    for (const name in alternateAirports) {
        const option = document.createElement('option');
        option.value = name;
        option.textContent = name;
        alternateSelect.appendChild(option);
    }

    // Show the frequency after selection.
    alternateSelect.addEventListener('change', function () {
        const selectedName = this.value;
        radioInput.value = alternateAirports[selectedName] || '';
    });
}

function getFuelNumber(id) {
    const field = document.getElementById(id);
    const value = field ? parseFloat(field.value) : 0;

    return Number.isFinite(value) ? value : 0;
}

function calculateFuel() {
    const totalRequiredFuelOutput = document.getElementById('total_required_fuel');
    const remainingFuelOutput = document.getElementById('remaining_fuel');
    const fuelStatusOutput = document.getElementById('fuel_status');

    // Stop safely if the fuel result fields are not available on the page.
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

    // If the field is inside a collapsed details panel, open that panel first.
    const parentDetails = element.closest('details');

    if (parentDetails && !parentDetails.open) {
        parentDetails.open = true;
    }

    // Wait one frame so the browser can render the opened panel before measuring.
    requestAnimationFrame(() => {
        // Keep the active field visible without jumping to the top of the page.
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
            inline: 'nearest'
        });

        const rect = element.getBoundingClientRect();

        // Highlight the current element.
        steps.forEach(step => step.removeAttribute('data-highlight'));
        element.setAttribute('data-highlight', 'true');

        // Show the overlay and tooltip.
        overlay.style.display = 'block';
        tooltip.style.display = 'block';
        text.textContent = element.dataset.text;

        // Position the tooltip below the element.
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

document.addEventListener('DOMContentLoaded', function () {
    const successMessage = document.querySelector('.success-message');

    if (successMessage) {
        setTimeout(() => {
            successMessage.style.display = 'none';

            const url = new URL(window.location.href);
            url.searchParams.delete('success');
            window.history.replaceState({}, '', url.toString());
        }, 4000);
    }
});

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