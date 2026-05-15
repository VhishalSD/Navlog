<?php

class AircraftTimingTableRenderer
{
    public static function render(array $aircraftView): string
    {
        $tasLabel = ($aircraftView['tas'] ?? '') !== '' ? ViewHelper::e($aircraftView['tas']) . 'kt' : '';

        return '
    <table id="table1" data-step="3" data-text="Review and enter the aircraft, pilot and timing details for the selected flight.">
        <tr>
            <td>Date</td>
            <td colspan="3"><input class="table-full-input" type="date" value="' . ViewHelper::e($aircraftView['date'] ?? '') . '" readonly/></td>
            <td>Tacho_beg:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_beg" value="' . ViewHelper::e($aircraftView['tachoBeg'] ?? '') . '"/></td>
            <td>Tacho_end:</td>
            <td><input type="text" form="aircraft-timing-table-form" name="tacho_end" value="' . ViewHelper::e($aircraftView['tachoEnd'] ?? '') . '"/></td>
            <td>Pilot</td>
            <td><input type="text" form="aircraft-timing-table-form" name="pilot" value="' . ViewHelper::e($aircraftView['pilot'] ?? '') . '"/></td>
            <td>Altitudes</td>
            <td class="table-cell-narrow">OAT</td>
            <td>IAS</td>
            <td>TAS</td>
        </tr>
        <tr>
            <td>Dept</td>
            <td><input type="text" value="' . ViewHelper::e($aircraftView['departure'] ?? '') . '" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="' . ViewHelper::e($aircraftView['departureElevation'] ?? '') . '" readonly/></td>
            <td>Off-blocks:</td>
            <td><input type="time" form="aircraft-timing-table-form" name="offblocks" value="' . ViewHelper::e($aircraftView['offblocks'] ?? '') . '"/></td>
            <td>Engine_off</td>
            <td><input type="time" form="aircraft-timing-table-form" name="engine_off" value="' . ViewHelper::e($aircraftView['engineOff'] ?? '') . '"/></td>
            <td>Acft_type</td>
            <td><input class="typeInput" id="type" form="aircraft-timing-table-form" name="aircraft_type" value="' . ViewHelper::e($aircraftView['aircraftType'] ?? '') . '" readonly/></td>
            <td><input type="number" value="' . ViewHelper::e($aircraftView['departureAltitude'] ?? '') . '" readonly/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="oat" value="' . ViewHelper::e($aircraftView['oat'] ?? '') . '"/></td>
            <td><input type="number" form="aircraft-timing-table-form" name="ias" value="' . ViewHelper::e($aircraftView['ias'] ?? '') . '"/></td>
            <td><input type="text" value="' . $tasLabel . '" readonly/></td>
        </tr>
        <tr>
            <td>Dest</td>
            <td><input type="text" value="' . ViewHelper::e($aircraftView['destination'] ?? '') . '" readonly/></td>
            <td>elev:</td>
            <td><input class="elevationInput" value="' . ViewHelper::e($aircraftView['destinationElevation'] ?? '') . '" readonly/></td>
            <td>Take-off time:</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="takeoff_time" value="' . ViewHelper::e($aircraftView['takeoffTime'] ?? '') . '"/></td>
            <td>Landing-time</td>
            <td><input class="time-input" type="time" form="aircraft-timing-table-form" name="landing_time" value="' . ViewHelper::e($aircraftView['landingTime'] ?? '') . '"/></td>
            <td>Reg</td>
            <td>
                <select form="aircraft-timing-table-form" name="registration" id="table_aircraft_registration" class="aircraftSelect">
                    ' . AircraftRegistrationRenderer::renderOptions($aircraftView['registrations'] ?? [], $aircraftView['registration'] ?? '') . '
                </select>
            </td>
            <td><input type="number" value="' . ViewHelper::e($aircraftView['destinationAltitude'] ?? '') . '" readonly/></td>
            <td><input type="number" value="' . ViewHelper::e($aircraftView['oat'] ?? '') . '" readonly/></td>
            <td><input type="number" value="' . ViewHelper::e($aircraftView['ias'] ?? '') . '" readonly/></td>
            <td><input type="text" value="' . $tasLabel . '" readonly/></td>
        </tr>
    </table>';
    }
}
