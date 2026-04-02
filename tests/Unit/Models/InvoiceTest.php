<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Models\Vendor;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'vendor_id' => '1',
            'invoice_status_id' => '1',
            'invoice_number' => 'INV-2024-001',
            'invoice_date' => '2024-03-01',
            'due_date' => '2024-03-31',
            'amount' => '150000',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    private function validVendor(): Vendor
    {
        return Vendor::fromRow([
            'id' => '1',
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
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

    private function validItem(): InvoiceItem
    {
        return InvoiceItem::fromRow([
            'id' => '1',
            'invoice_id' => '1',
            'description' => 'Steel brackets',
            'quantity' => '5.0000',
            'unit_price' => '20000',
            'total' => '100000',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function validHistory(): InvoiceStatusHistory
    {
        return new InvoiceStatusHistory(
            id: 1,
            invoiceId: 1,
            invoiceStatusId: 1,
            changedBy: 'system',
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $this->validStatus(),
        );
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $invoice = Invoice::fromRow($this->validRow());

        $this->assertSame(1, $invoice->id);
        $this->assertSame(1, $invoice->vendorId);
        $this->assertSame(1, $invoice->invoiceStatusId);
        $this->assertSame('INV-2024-001', $invoice->invoiceNumber);
        $this->assertSame('2024-03-01', $invoice->invoiceDate);
        $this->assertSame('2024-03-31', $invoice->dueDate);
        $this->assertSame(150000, $invoice->amount);
    }

    public function testFromRowCastsIdFieldsToInt(): void
    {
        $invoice = Invoice::fromRow($this->validRow());

        $this->assertIsInt($invoice->id);
        $this->assertIsInt($invoice->vendorId);
        $this->assertIsInt($invoice->invoiceStatusId);
        $this->assertIsInt($invoice->amount);
    }

    public function testFromRowSetsNullableRelationshipsToNull(): void
    {
        $invoice = Invoice::fromRow($this->validRow());

        $this->assertNull($invoice->vendor);
        $this->assertNull($invoice->status);
        $this->assertEmpty($invoice->items);
        $this->assertEmpty($invoice->history);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['invoice_number']);

        Invoice::fromRow($row);
    }

    public function testToArrayReturnsNullForUnhydratedRelationships(): void
    {
        $invoice = Invoice::fromRow($this->validRow());
        $array = $invoice->toArray();

        $this->assertNull($array['vendor']);
        $this->assertNull($array['status']);
        $this->assertNull($array['items']);
        $this->assertNull($array['history']);
    }

    public function testToArrayReturnsHydratedVendor(): void
    {
        $invoice = new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            vendor: $this->validVendor(),
        );

        $array = $invoice->toArray();

        $this->assertIsArray($array['vendor']);
        $this->assertSame('Acme Supplies Ltd', $array['vendor']['name']);
    }

    public function testToArrayReturnsHydratedStatus(): void
    {
        $invoice = new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $this->validStatus(),
        );

        $array = $invoice->toArray();

        $this->assertIsArray($array['status']);
        $this->assertSame('pending', $array['status']['slug']);
    }

    public function testToArrayReturnsHydratedItems(): void
    {
        $invoice = new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            items: [$this->validItem()],
        );

        $array = $invoice->toArray();

        $this->assertIsArray($array['items']);
        $this->assertCount(1, $array['items']);
        $this->assertSame('Steel brackets', $array['items'][0]['description']);
    }

    public function testToArrayReturnsHydratedHistory(): void
    {
        $invoice = new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            history: [$this->validHistory()],
        );

        $array = $invoice->toArray();

        $this->assertIsArray($array['history']);
        $this->assertCount(1, $array['history']);
        $this->assertSame('system', $array['history'][0]['changed_by']);
    }

    public function testToArrayReturnsCorrectStructureForBareInvoice(): void
    {
        $invoice = Invoice::fromRow($this->validRow());
        $array = $invoice->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('invoice_number', $array);
        $this->assertArrayHasKey('invoice_date', $array);
        $this->assertArrayHasKey('due_date', $array);
        $this->assertArrayHasKey('amount', $array);
        $this->assertArrayHasKey('created_at', $array);
        $this->assertArrayHasKey('updated_at', $array);
        $this->assertArrayHasKey('vendor', $array);
        $this->assertArrayHasKey('status', $array);
        $this->assertArrayHasKey('items', $array);
        $this->assertArrayHasKey('history', $array);
    }
}