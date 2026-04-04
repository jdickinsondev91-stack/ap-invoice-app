<?php

namespace App\Helpers;

use function bcadd;
use function bcdiv;
use function bcmul;

class MoneyHelper
{
    public const DEFAULT_DECIMAL_PLACES = 2;

    public static function toDecimal(int $pence, int $decimalPlaces = self::DEFAULT_DECIMAL_PLACES): string
    {
        return number_format(
            (float) bcdiv((string) $pence, '100', $decimalPlaces + 2),
            $decimalPlaces
        );
    }

    public static function toPence(string|float|int $amount): int
    {
        $multiplied = bcmul((string) $amount, '100', 4);

        return (int) bcadd($multiplied, '0.5', 0);
    }

    public static function multiply(int $value, string|int $multiplier): int 
    {
        $result = bcmul((string) $value, (string) $multiplier, 4);

        return (int) bcadd($result, '0.5', 0);
    }
}
