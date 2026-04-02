<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use PHPUnit\Framework\TestCase;

class InvoiceStatusHistoryTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'invoice_id' => '1',
            'invoice_status_id' => '1',
            'changed_by' => 'jane.smith',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    private function validStatus(): InvoiceStatus
    {
        return InvoiceStatus::fromRow([
            'id' => '1',
            'name' => 'Pending',
            'slug' => 'pending',
            'description' => 'Awaiting review and approval',
            'sort_order' => '1',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $history = InvoiceStatusHistory::fromRow($this->validRow());

        $this->assertSame(1, $history->id);
        $this->assertSame(1, $history->invoiceId);
        $this->assertSame(1, $history->invoiceStatusId);
        $this->assertSame('jane.smith', $history->changedBy);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['changed_by']);

        InvoiceStatusHistory::fromRow($row);
    }

    public function testToArrayReturnsNullStatusWhenNotHydrated(): void
    {
        $history = InvoiceStatusHistory::fromRow($this->validRow());

        $this->assertNull($history->toArray()['status']);
    }

    public function testToArrayReturnsHydratedStatus(): void
    {
        $history = new InvoiceStatusHistory(
            id: 1,
            invoiceId: 1,
            invoiceStatusId: 1,
            changedBy: 'jane.smith',
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $this->validStatus(),
        );

        $array = $history->toArray();

        $this->assertIsArray($array['status']);
        $this->assertSame('pending', $array['status']['slug']);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $history = new InvoiceStatusHistory(
            id: 1,
            invoiceId: 1,
            invoiceStatusId: 1,
            changedBy: 'jane.smith',
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $this->validStatus(),
        );

        $array = $history->toArray();

        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('changed_by', $array);
        $this->assertArrayHasKey('changed_at', $array);
        $this->assertSame('jane.smith', $array['changed_by']);
        $this->assertSame('2024-03-01 09:00:00', $array['changed_at']);
    }
}