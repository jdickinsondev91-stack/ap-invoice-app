<?php

namespace App\Tests\Unit\DTOs;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\CreateInvoiceItemDTO;
use PHPUnit\Framework\TestCase;

class CreateInvoiceDTOTest extends TestCase
{
    private function validData(): array
    {
        return [
            'vendor_id' => 1,
            'invoice_number' => 'INV-2024-001',
            'invoice_date' => '2024-03-01',
            'due_date' => '2024-03-31',
            'items' => [
                [
                    'description' => 'Steel brackets',
                    'quantity' => '5.0000',
                    'unit_price' => 20000,
                ],
            ],
        ];
    }

    public function testFromArrayCreatesWithCorrectValues(): void
    {
        $dto = CreateInvoiceDTO::fromArray($this->validData());

        $this->assertSame(1, $dto->vendorId);
        $this->assertSame('INV-2024-001', $dto->invoiceNumber);
        $this->assertSame('2024-03-01', $dto->invoiceDate);
        $this->assertSame('2024-03-31', $dto->dueDate);
    }

    public function testFromArrayMapsItemsToCreateInvoiceItemDTOs(): void
    {
        $dto = CreateInvoiceDTO::fromArray($this->validData());

        $this->assertCount(1, $dto->items);
        $this->assertContainsOnlyInstancesOf(CreateInvoiceItemDTO::class, $dto->items);
        $this->assertSame('Steel brackets', $dto->items[0]->description);
    }

    public function testFromArrayWithNoItemsProducesEmptyArray(): void
    {
        $data = $this->validData();
        unset($data['items']);

        $dto = CreateInvoiceDTO::fromArray($data);

        $this->assertSame([], $dto->items);
    }

    public function testFromArrayThrowsForMissingVendorId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('vendor_id is required');

        $data = $this->validData();
        unset($data['vendor_id']);
        CreateInvoiceDTO::fromArray($data);
    }

    public function testFromArrayThrowsForMissingInvoiceNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invoice_number is required');

        $data = $this->validData();
        unset($data['invoice_number']);
        CreateInvoiceDTO::fromArray($data);
    }

    public function testFromArrayThrowsForMissingInvoiceDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invoice_date is required');

        $data = $this->validData();
        unset($data['invoice_date']);
        CreateInvoiceDTO::fromArray($data);
    }

    public function testFromArrayThrowsForMissingDueDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('due_date is required');

        $data = $this->validData();
        unset($data['due_date']);
        CreateInvoiceDTO::fromArray($data);
    }

    public function testFromArrayThrowsWhenItemIsMissingField(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $data = $this->validData();
        unset($data['items'][0]['description']);
        CreateInvoiceDTO::fromArray($data);
    }
}
