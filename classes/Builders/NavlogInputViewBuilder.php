<?php

class NavlogInputViewBuilder
{
    public static function build(array $leg, string|int $rowKey): array
    {
        $escapedRowKey = ViewHelper::e((string)$rowKey);

        return [
            'rowKey' => $escapedRowKey,

            'timeAcc' => ViewHelper::e($leg['time_acc'] ?? ''),
            'timeInt' => ViewHelper::e($leg['time_int'] ?? ''),

            'etoName' => 'legs[' . $escapedRowKey . '][eto]',
            'retoName' => 'legs[' . $escapedRowKey . '][reto]',
            'atoName' => 'legs[' . $escapedRowKey . '][ato]',
            'mefName' => 'legs[' . $escapedRowKey . '][mef]',
            'cruiseName' => 'legs[' . $escapedRowKey . '][cruise]',
            'checkpointLocationName' => 'legs[' . $escapedRowKey . '][checkpoint_location]',
            'checkpointFrequencyName' => 'legs[' . $escapedRowKey . '][checkpoint_frequency]',
            'variationName' => 'legs[' . $escapedRowKey . '][variation]',
            'windDirName' => 'legs[' . $escapedRowKey . '][wind_dir]',
            'windVName' => 'legs[' . $escapedRowKey . '][wind_v]',
            'ttName' => 'legs[' . $escapedRowKey . '][tt]',
            'distIntName' => 'legs[' . $escapedRowKey . '][dist_int]',

            'eto' => ViewHelper::e($leg['ETO'] ?? ''),
            'reto' => ViewHelper::e($leg['RETO'] ?? ''),
            'ato' => ViewHelper::e($leg['ATO'] ?? ''),
            'mef' => ViewHelper::e($leg['MEF'] ?? ''),
            'cruise' => ViewHelper::e($leg['cruise'] ?? ''),
            'checkpointLocation' => ViewHelper::e($leg['checkpoint_location'] ?? ''),
            'checkpointFrequency' => ViewHelper::e($leg['checkpoint_frequency'] ?? ''),
            'mh' => ViewHelper::e($leg['MH'] ?? ''),
            'variation' => ViewHelper::e($leg['var'] ?? ''),
            'th' => ViewHelper::e($leg['TH'] ?? ''),
            'wca' => ViewHelper::e($leg['WCA'] ?? ''),
            'windDir' => ViewHelper::e($leg['wind_dir'] ?? ''),
            'windV' => ViewHelper::e($leg['wind_v'] ?? ''),
            'tt' => ViewHelper::e($leg['tt'] ?? ''),
            'distInt' => ViewHelper::e($leg['dist_int'] ?? ''),
            'distAcc' => ViewHelper::e($leg['dist_acc'] ?? ''),
            'gs' => ViewHelper::e($leg['gs'] ?? ''),
        ];
    }
}
