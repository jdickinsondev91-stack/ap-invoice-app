<?php

namespace App\Helpers;

class MoneyHelper
{
    public static function calculateTotal(string $quantity, int $unitPrice): int
    {
        return (int) round((float) $quantity * $unitPrice);
    }

    /**
     * @param array{quantity: string, unitPrice: int}[] $items
     */
    public static function sumItemTotals(array $items): int
    {
        return array_sum(
            array_map(
                fn(array $item) => self::calculateTotal($item['quantity'], $item['unitPrice']),
                $items
            )
        );
    }
}
