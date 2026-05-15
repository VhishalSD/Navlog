<?php

class NavlogRowViewBuilder
{
    public static function buildRows(array $selectedLegs, array $databaseLegRows): array
    {
        $rows = [];
        $visibleRows = max(4, count($selectedLegs));

        for ($rowNumber = 1; $rowNumber <= $visibleRows; $rowNumber++) {
            $leg = $selectedLegs[$rowNumber - 1] ?? [];
            $isEvenRow = $rowNumber % 2 === 0;
            $databaseLeg = $databaseLegRows[$rowNumber - 1] ?? null;

            $rows[] = [
                'rowNumber' => $rowNumber,
                'leg' => $leg,
                'blueCellClass' => $isEvenRow ? 'cell-blue-dark' : 'cell-blue-light',
                'pinkCellClass' => $isEvenRow ? 'cell-pink-dark' : 'cell-pink-light',
                'databaseLeg' => $databaseLeg,
                'rowKey' => $leg['_row_key'] ?? ($databaseLeg !== null ? (int)$databaseLeg['idLeg'] : 'new_' . $rowNumber),
            ];
        }

        return $rows;
    }
}
