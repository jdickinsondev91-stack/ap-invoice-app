<?php

namespace App\Tests\Unit\Helpers;

use App\Helpers\MoneyHelper;
use PHPUnit\Framework\TestCase;

class MoneyHelperTest extends TestCase
{
    public function testCalculateTotalWithWholeQuantity(): void
    {
        // 5 units at £200.00 (20000p) = £1000.00 (100000p)
        $this->assertSame(100000, MoneyHelper::calculateTotal('5.0000', 20000));
    }

    public function testCalculateTotalWithDecimalQuantity(): void
    {
        // 2.5 units at £190.00 (19000p) = £475.00 (47500p)
        $this->assertSame(47500, MoneyHelper::calculateTotal('2.5000', 19000));
    }

    public function testCalculateTotalRoundsToNearestPenny(): void
    {
        // 1.3333 units at £3.00 (300p) = 399.99p → 400p
        $this->assertSame(400, MoneyHelper::calculateTotal('1.3333', 300));
    }

    public function testCalculateTotalReturnsInt(): void
    {
        $result = MoneyHelper::calculateTotal('3.0000', 1000);

        $this->assertIsInt($result);
    }

    public function testSumItemTotalsWithMultipleItems(): void
    {
        $items = [
            ['quantity' => '10.0000', 'unitPrice' => 5000],  // 50000p
            ['quantity' => '5.0000',  'unitPrice' => 20000], // 100000p
        ];

        $this->assertSame(150000, MoneyHelper::sumItemTotals($items));
    }

    public function testSumItemTotalsWithSingleItem(): void
    {
        $items = [
            ['quantity' => '8.0000', 'unitPrice' => 15000], // 120000p
        ];

        $this->assertSame(120000, MoneyHelper::sumItemTotals($items));
    }

    public function testSumItemTotalsWithDecimalQuantities(): void
    {
        $items = [
            ['quantity' => '2.5000', 'unitPrice' => 19000], // 47500p
            ['quantity' => '1.3333', 'unitPrice' => 300],   // 400p
        ];

        $this->assertSame(47900, MoneyHelper::sumItemTotals($items));
    }

    public function testSumItemTotalsReturnsZeroForEmptyArray(): void
    {
        $this->assertSame(0, MoneyHelper::sumItemTotals([]));
    }
}
