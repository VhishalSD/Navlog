<?php

class NavlogTableViewBuilder
{
    public static function build(array $selectedLegs, array $databaseLegRows): array
    {
        $rows = NavlogRowViewBuilder::buildRows($selectedLegs, $databaseLegRows);

        foreach ($rows as &$row) {
            $row['input'] = NavlogInputViewBuilder::build($row['leg'], $row['rowKey']);
        }

        unset($row);

        return $rows;
    }
}
