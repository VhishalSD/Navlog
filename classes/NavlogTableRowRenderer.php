<?php

class NavlogTableRowRenderer
{
    public static function renderRows(array $navlogTableRows, array $selectedFlightView): string
    {
        $html = '';

        foreach ($navlogTableRows as $navlogRow) {
            $html .= self::renderRow($navlogRow, $selectedFlightView);
        }

        return $html;
    }

    private static function renderRow(array $navlogRow, array $selectedFlightView): string
    {
        $rowNumber = $navlogRow['rowNumber'];
        $blueCellClass = $navlogRow['blueCellClass'];
        $pinkCellClass = $navlogRow['pinkCellClass'];
        $databaseLeg = $navlogRow['databaseLeg'];
        $navlogInput = $navlogRow['input'];

        $html = '<tr>';

        $html .= '<td>';
        $html .= '<input class="navlog-input ' . ViewHelper::e($blueCellClass) . '" type="text" value="' . (int)$rowNumber . ' &darr;" readonly/>';

        if ($databaseLeg !== null) {
            $html .= '<div class="leg-row-actions">';
            $html .= '<button type="button" onclick="openDeleteLegModal(' . (int)$selectedFlightView['id'] . ', ' . (int)$databaseLeg['idLeg'] . ')">Delete</button>';
            $html .= '</div>';
        }

        $html .= '</td>';

        $html .= self::readonlyInput($pinkCellClass, $navlogInput['timeAcc']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['timeInt']);
        $html .= self::formInput($blueCellClass, $navlogInput['etoName'], 'eto', $navlogInput['eto']);
        $html .= self::formInput($blueCellClass, $navlogInput['retoName'], 'reto', $navlogInput['reto']);
        $html .= self::formInput($blueCellClass, $navlogInput['atoName'], 'ato', $navlogInput['ato']);
        $html .= self::formInput($blueCellClass, $navlogInput['mefName'], 'mef', $navlogInput['mef']);
        $html .= self::formInput($blueCellClass, $navlogInput['cruiseName'], 'cruise', $navlogInput['cruise']);

        $html .= '<td class="checkpoint-cell">';
        $html .= '<span class="checkpoint-hover" data-tooltip="' . ViewHelper::e($navlogInput['checkpointLocation']) . '">';
        $html .= '<input id="leg' . (int)$rowNumber . 'Name" class="navlog-input table-full-input ' . ViewHelper::e($blueCellClass) . '" type="text" form="navlog-table-form" name="' . ViewHelper::e($navlogInput['checkpointLocationName']) . '" data-field="checkpoint_location" value="' . ViewHelper::e($navlogInput['checkpointLocation']) . '"/>';
        $html .= '</span>';
        $html .= '</td>';

        $html .= self::formInput($blueCellClass . ' table-full-input', $navlogInput['checkpointFrequencyName'], 'checkpoint_frequency', $navlogInput['checkpointFrequency']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['mh']);
        $html .= self::formInput($blueCellClass, $navlogInput['variationName'], 'variation', $navlogInput['variation']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['th']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['wca']);
        $html .= self::formInput($blueCellClass, $navlogInput['windDirName'], 'wind_dir', $navlogInput['windDir']);
        $html .= self::formInput($blueCellClass, $navlogInput['windVName'], 'wind_v', $navlogInput['windV']);
        $html .= self::formInput($blueCellClass, $navlogInput['ttName'], 'tt', $navlogInput['tt']);
        $html .= self::formInput($blueCellClass, $navlogInput['distIntName'], 'dist_int', $navlogInput['distInt']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['distAcc']);
        $html .= self::readonlyInput($pinkCellClass, $navlogInput['gs']);

        $html .= '</tr>';

        return $html;
    }

    private static function readonlyInput(string $cellClass, string $value): string
    {
        return '<td><input class="navlog-input ' . ViewHelper::e($cellClass) . '" type="text" value="' . ViewHelper::e($value) . '" readonly/></td>';
    }

    private static function formInput(string $cellClass, string $name, string $field, string $value): string
    {
        return '<td><input class="navlog-input ' . ViewHelper::e($cellClass) . '" type="text" form="navlog-table-form" name="' . ViewHelper::e($name) . '" data-field="' . ViewHelper::e($field) . '" value="' . ViewHelper::e($value) . '"/></td>';
    }
}
