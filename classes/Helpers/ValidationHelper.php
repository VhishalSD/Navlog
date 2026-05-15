<?php

class ValidationHelper
{
    public static function isValidIcaoCode(string $icaoCode): bool
    {
        return preg_match('/^[A-Z]{4}$/', $icaoCode) === 1;
    }

    public static function isValidDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);

        return $dateTime !== false && $dateTime->format('Y-m-d') === $date;
    }

    public static function isInRange(int $value, int $min, int $max): bool
    {
        return $value >= $min && $value <= $max;
    }

    public static function isEmptyNavlogTableRow(array $row): bool
    {
        foreach (['checkpoint_location', 'variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'] as $fieldName) {
            if (trim((string)($row[$fieldName] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public static function isCompletelyEmptyNavlogTableRow(array $row): bool
    {
        foreach (['eto', 'reto', 'ato', 'mef', 'cruise', 'checkpoint_location', 'checkpoint_frequency', 'variation', 'wind_dir', 'wind_v', 'tt', 'dist_int'] as $fieldName) {
            if (trim((string)($row[$fieldName] ?? '')) !== '') {
                return false;
            }
        }

        return true;
    }

    public static function getNavlogTableInt(array $row, string $fieldName, string $label, int $min, int $max, array &$validationErrors): int
    {
        $rawValue = trim((string)($row[$fieldName] ?? ''));

        if ($rawValue === '') {
            $validationErrors[] = $label . ' is required in every filled NAVLOG row.';
            return 0;
        }

        $value = filter_var($rawValue, FILTER_VALIDATE_INT);

        if ($value === false || !self::isInRange((int)$value, $min, $max)) {
            $validationErrors[] = $label . ' must be a whole number between ' . $min . ' and ' . $max . '.';
            return 0;
        }

        return (int)$value;
    }
}
