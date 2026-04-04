<?php

namespace App\Tests\Unit\DTOs;

use App\DTOs\CreateInvoiceItemDTO;
use PHPUnit\Framework\TestCase;

class CreateInvoiceItemDTOTest extends TestCase
{
    private function validData(): array
    {
        return [
            'description' => 'Steel brackets',
            'quantity' => '5.0000',
            'unit_price' => 20000,
        ];
    }

    public function testFromArrayCreatesWithCorrectValues(): void
    {
        $dto = CreateInvoiceItemDTO::fromArray($this->validData());

        $this->assertSame('Steel brackets', $dto->description);
        $this->assertSame('5.0000', $dto->quantity);
        $this->assertSame(20000, $dto->unitPrice);
    }

    public function testFromArrayCastsUnitPriceToInt(): void
    {
        $dto = CreateInvoiceItemDTO::fromArray(array_merge($this->validData(), ['unit_price' => '20000']));

        $this->assertIsInt($dto->unitPrice);
    }

    public function testFromArrayThrowsForMissingDescription(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('description is required');

        $data = $this->validData();
        unset($data['description']);
        CreateInvoiceItemDTO::fromArray($data);
    }

    public function testFromArrayThrowsForMissingQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('quantity is required');

        $data = $this->validData();
        unset($data['quantity']);
        CreateInvoiceItemDTO::fromArray($data);
    }

    public function testFromArrayThrowsForMissingUnitPrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('unit_price is required');

        $data = $this->validData();
        unset($data['unit_price']);
        CreateInvoiceItemDTO::fromArray($data);
    }
}
