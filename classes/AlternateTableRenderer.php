<?php

class AlternateTableRenderer
{
    public static function render(): string
    {
        return '
    <table id="table3" class="table-continuation">
        <tr>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="alternate-label-input" type="text" value="ALTERNATE"/></td>
            <td><input class="table-full-input" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
            <td><input class="cell-pink-light" type="text"/></td>
        </tr>
        <tr>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td>
                <select id="airportSelect" class="alternate-select cell-blue-dark">
                    <option value="">Select alternate</option>
                </select>
            </td>
            <td><input id="radioInput" class="alternate-radio-input cell-blue-dark" readonly /></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-blue-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
            <td><input class="cell-pink-dark" type="text"/></td>
        </tr>
    </table>';
    }
}
