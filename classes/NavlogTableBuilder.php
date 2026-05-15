<?php

class NavlogTableBuilder
{
    public static function buildSubmittedRowsForDisplay(array $tableRows, int $tas): array
    {
        $displayRows = [];
        $timeAcc = 0;
        $distAcc = 0;

        foreach ($tableRows as $rowKey => $row) {
            if (!is_array($row) || ValidationHelper::isCompletelyEmptyNavlogTableRow($row)) {
                continue;
            }

            $variation = (int)trim((string)($row['variation'] ?? '0'));
            $windDir = (int)trim((string)($row['wind_dir'] ?? '0'));
            $windV = (int)trim((string)($row['wind_v'] ?? '0'));
            $tt = (int)trim((string)($row['tt'] ?? '0'));
            $distInt = (int)trim((string)($row['dist_int'] ?? '0'));

            $calculatedValues = Leg::calculateNavlogValues($variation, $windDir, $windV, $tt, $distInt, $tas);

            $timeAcc += $calculatedValues['time_int'];
            $distAcc += $distInt;

            $displayRows[] = [
                '_row_key' => (string)$rowKey,
                'time_acc' => $timeAcc,
                'time_int' => $calculatedValues['time_int'],
                'ETO' => trim((string)($row['eto'] ?? '')),
                'RETO' => trim((string)($row['reto'] ?? '')),
                'ATO' => trim((string)($row['ato'] ?? '')),
                'MEF' => trim((string)($row['mef'] ?? '')),
                'cruise' => trim((string)($row['cruise'] ?? '')),
                'checkpoint_location' => trim((string)($row['checkpoint_location'] ?? '')),
                'checkpoint_frequency' => trim((string)($row['checkpoint_frequency'] ?? '')),
                'MH' => $calculatedValues['mh'],
                'var' => trim((string)($row['variation'] ?? '')),
                'TH' => $calculatedValues['th'],
                'WCA' => $calculatedValues['wca'],
                'wind_dir' => trim((string)($row['wind_dir'] ?? '')),
                'wind_v' => trim((string)($row['wind_v'] ?? '')),
                'tt' => trim((string)($row['tt'] ?? '')),
                'dist_int' => trim((string)($row['dist_int'] ?? '')),
                'dist_acc' => $distAcc,
                'gs' => $calculatedValues['gs'],
            ];
        }

        return $displayRows;
    }
}
