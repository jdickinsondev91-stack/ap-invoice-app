<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\DuplicateInvoiceFlag;
use PHPUnit\Framework\TestCase;

class DuplicateInvoiceFlagTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'invoice_id' => '1',
            'matched_invoice_id' => '2',
            'reason' => 'Same vendor, amount and invoice date',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $flag = DuplicateInvoiceFlag::fromRow($this->validRow());

        $this->assertSame(1, $flag->id);
        $this->assertSame(1, $flag->invoiceId);
        $this->assertSame(2, $flag->matchedInvoiceId);
        $this->assertSame('Same vendor, amount and invoice date', $flag->reason);
    }

    public function testFromRowCastsIdsToInt(): void
    {
        $flag = DuplicateInvoiceFlag::fromRow($this->validRow());

        $this->assertIsInt($flag->id);
        $this->assertIsInt($flag->invoiceId);
        $this->assertIsInt($flag->matchedInvoiceId);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['reason']);

        DuplicateInvoiceFlag::fromRow($row);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $flag = DuplicateInvoiceFlag::fromRow($this->validRow());

        $this->assertSame([
            'id' => 1,
            'invoice_id' => 1,
            'matched_invoice_id' => 2,
            'reason' => 'Same vendor, amount and invoice date',
            'created_at' => '2024-03-01 09:00:00',
        ], $flag->toArray());
    }
}