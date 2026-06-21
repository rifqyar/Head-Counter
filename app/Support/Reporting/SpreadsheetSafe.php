<?php

namespace App\Support\Reporting;

class SpreadsheetSafe
{
    public static function value(mixed $value): mixed
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }

    public static function row(array $row): array
    {
        return array_map(fn ($value) => self::value($value), $row);
    }
}
