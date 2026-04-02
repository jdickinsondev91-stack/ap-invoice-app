<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\InvoiceItem;
use PHPUnit\Framework\TestCase;

class InvoiceItemTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'invoice_id' => '1',
            'description' => 'Industrial bolts (box of 100)',
            'quantity' => '10.0000',
            'unit_price' => '5000',
            'total' => '50000',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());

        $this->assertSame(1, $item->id);
        $this->assertSame(1, $item->invoiceId);
        $this->assertSame('Industrial bolts (box of 100)', $item->description);
        $this->assertSame('10.0000', $item->quantity);
        $this->assertSame(5000, $item->unitPrice);
        $this->assertSame(50000, $item->total);
    }

    public function testQuantityIsStoredAsString(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());

        $this->assertIsString($item->quantity);
    }

    public function testUnitPriceIsStoredAsInt(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());

        $this->assertIsInt($item->unitPrice);
    }

    public function testTotalIsStoredAsInt(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());

        $this->assertIsInt($item->total);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['unit_price']);

        InvoiceItem::fromRow($row);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());

        $this->assertSame([
            'id' => 1,
            'description' => 'Industrial bolts (box of 100)',
            'quantity' => '10.0000',
            'unit_price' => 5000,
            'total' => 50000,
        ], $item->toArray());
    }

    public function testToArrayExcludesInvoiceId(): void
    {
        $item = InvoiceItem::fromRow($this->validRow());
        $array = $item->toArray();

        $this->assertArrayNotHasKey('invoice_id', $array);
    }
}