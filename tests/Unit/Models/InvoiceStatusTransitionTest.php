<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\InvoiceStatusTransition;
use PHPUnit\Framework\TestCase;

class InvoiceStatusTransitionTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'from_invoice_status_id' => '1',
            'to_invoice_status_id' => '2',
        ];
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $transition = InvoiceStatusTransition::fromRow($this->validRow());

        $this->assertSame(1, $transition->id);
        $this->assertSame(1, $transition->fromInvoiceStatusId);
        $this->assertSame(2, $transition->toInvoiceStatusId);
    }

    public function testFromRowCastsAllFieldsToInt(): void
    {
        $transition = InvoiceStatusTransition::fromRow($this->validRow());

        $this->assertIsInt($transition->id);
        $this->assertIsInt($transition->fromInvoiceStatusId);
        $this->assertIsInt($transition->toInvoiceStatusId);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['from_invoice_status_id']);

        InvoiceStatusTransition::fromRow($row);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $transition = InvoiceStatusTransition::fromRow($this->validRow());

        $this->assertSame([
            'id' => 1,
            'from_invoice_status_id' => 1,
            'to_invoice_status_id' => 2,
        ], $transition->toArray());
    }
}