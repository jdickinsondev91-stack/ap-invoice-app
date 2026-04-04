<?php

namespace App\Tests\Unit\Helpers;

use App\Helpers\MoneyHelper;
use PHPUnit\Framework\TestCase;

class MoneyHelperTest extends TestCase
{
    public function testToDecimalConvertsWholePoounds(): void
    {
        $this->assertSame('10.00', MoneyHelper::toDecimal(1000));
    }

    public function testToDecimalConvertsWithPence(): void
    {
        $this->assertSame('15.50', MoneyHelper::toDecimal(1550));
    }

    public function testToDecimalReturnsTwoDecimalPlaces(): void
    {
        $this->assertSame('0.01', MoneyHelper::toDecimal(1));
    }

    public function testToDecimalHandlesLargeAmount(): void
    {
        $this->assertSame('1,500.00', MoneyHelper::toDecimal(150000));
    }

    public function testToPenceConvertsDecimalString(): void
    {
        $this->assertSame(1000, MoneyHelper::toPence('10.00'));
    }

    public function testToPenceConvertsFloat(): void
    {
        $this->assertSame(1550, MoneyHelper::toPence(15.50));
    }

    public function testToPenceConvertsInt(): void
    {
        $this->assertSame(1000, MoneyHelper::toPence(10));
    }

    public function testToPenceRoundsCorrectly(): void
    {
        // £10.005 → 1000.5p → 1001p
        $this->assertSame(1001, MoneyHelper::toPence('10.005'));
    }

    public function testToPenceAvoidsBinaryFloatPrecisionError(): void
    {
        // (float) '2.675' * 100 = 267.49999... in floating point — should be 268
        $this->assertSame(268, MoneyHelper::toPence('2.675'));
    }

    public function testToPenceReturnsInt(): void
    {
        $this->assertIsInt(MoneyHelper::toPence('10.00'));
    }

    public function testRoundTrip(): void
    {
        $original = 47500;
        $this->assertSame($original, MoneyHelper::toPence(MoneyHelper::toDecimal($original)));
    }

    public function testMultiplyWithWholeQuantity(): void
    {
        // 10 x 5000p = 50000p
        $this->assertSame(50000, MoneyHelper::multiply(5000, '10'));
    }

    public function testMultiplyWithDecimalQuantity(): void
    {
        // 2.5 x 19000p = 47500p
        $this->assertSame(47500, MoneyHelper::multiply(19000, '2.5000'));
    }

    public function testMultiplyWithIntQuantity(): void
    {
        // 5 x 20000p = 100000p
        $this->assertSame(100000, MoneyHelper::multiply(20000, 5));
    }

    public function testMultiplyReturnsInt(): void
    {
        $this->assertIsInt(MoneyHelper::multiply(5000, '10'));
    }

    public function testMultiplyRoundsCorrectly(): void
    {
        // 3 x 3333p = 9999p (no rounding needed)
        $this->assertSame(9999, MoneyHelper::multiply(3333, '3'));
    }

    public function testMultiplyWithFractionalResult(): void
    {
        // 0.3333 x 100p = 33.33p → 33.33 + 0.5 = 33.83 truncated = 33p
        $this->assertSame(33, MoneyHelper::multiply(100, '0.3333'));
    }
}
