<?php

class NavlogTableHeaderViewBuilder
{
    public static function buildGroups(): array
    {
        return [
            ['label' => 'Leg', 'colspan' => 1],
            ['label' => 'Time', 'colspan' => 2],
            ['label' => 'Schedule', 'colspan' => 3],
            ['label' => 'Alt/FL', 'colspan' => 2],
            ['label' => 'Checkpoints', 'colspan' => 2],
            ['label' => 'Headings', 'colspan' => 4],
            ['label' => 'Wind', 'colspan' => 2],
            ['label' => 'Dir.', 'colspan' => 1],
            ['label' => 'Dist.', 'colspan' => 2],
            ['label' => 'Spd', 'colspan' => 1],
        ];
    }

    public static function buildColumns(): array
    {
        return [
            ['label' => 'no.', 'tooltip' => ''],
            ['label' => 'Acc.', 'tooltip' => ''],
            ['label' => 'Int.', 'tooltip' => ''],
            ['label' => 'ETO', 'tooltip' => 'Estimated Time Overhead: the estimated time when the aircraft is overhead a checkpoint.'],
            ['label' => 'RETO', 'tooltip' => 'Revised Estimated Time Overhead: the updated estimated overhead time when the original ETO changes.'],
            ['label' => 'ATO', 'tooltip' => 'Actual Time Overhead: the actual time when the aircraft is overhead a checkpoint.'],
            ['label' => 'MEF', 'tooltip' => ''],
            ['label' => 'Cruise', 'tooltip' => ''],
            ['label' => '__Checkpoint__', 'tooltip' => ''],
            ['label' => 'Frequency', 'tooltip' => ''],
            ['label' => 'MH', 'tooltip' => ''],
            ['label' => 'var.', 'tooltip' => ''],
            ['label' => 'TH', 'tooltip' => ''],
            ['label' => 'WCA', 'tooltip' => ''],
            ['label' => 'w', 'tooltip' => ''],
            ['label' => 'V', 'tooltip' => ''],
            ['label' => 'TT', 'tooltip' => ''],
            ['label' => 'Int.', 'tooltip' => ''],
            ['label' => 'Acc', 'tooltip' => ''],
            ['label' => 'GS', 'tooltip' => ''],
        ];
    }
}
