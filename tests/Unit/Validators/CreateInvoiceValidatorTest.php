<?php

namespace App\Tests\Unit\Validators;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\CreateInvoiceItemDTO;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\Vendor;
use App\Validators\CreateInvoiceValidator;
use PHPUnit\Framework\TestCase;

class CreateInvoiceValidatorTest extends TestCase
{
    private CreateInvoiceValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CreateInvoiceValidator();
    }

    private function validDTO(): CreateInvoiceDTO
    {
        return new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO(
                    description: 'Steel brackets',
                    quantity: '5.0000',
                    unitPrice: 20000,
                ),
            ],
        );
    }

    private function validVendor(): Vendor
    {
        return Vendor::fromRow([
            'id'         => '1',
            'name'       => 'Acme Supplies Ltd',
            'email'      => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function existingInvoice(): Invoice
    {
        return Invoice::fromRow([
            'id'                => '1',
            'vendor_id'         => '1',
            'invoice_status_id' => '1',
            'invoice_number'    => 'INV-2024-001',
            'invoice_date'      => '2024-03-01',
            'due_date'          => '2024-03-31',
            'amount'            => '150000',
            'created_at'        => '2024-03-01 09:00:00',
            'updated_at'        => '2024-03-01 09:00:00',
        ]);
    }

    public function testValidDataPassesValidation(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate($this->validDTO(), $this->validVendor(), []);
    }

    public function testThrowsWhenVendorIsNull(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Vendor with ID 1 not found.');

        $this->validator->validate($this->validDTO(), null, []);
    }

    public function testThrowsWhenPotentialDuplicatesExist(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invoice number 'INV-2024-001' already exists for vendor 'Acme Supplies Ltd'.");

        $this->validator->validate($this->validDTO(), $this->validVendor(), [$this->existingInvoice()]);
    }

    public function testThrowsWhenItemsIsEmpty(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('An invoice must have at least one item.');

        $dto = new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-002',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [],
        );

        $this->validator->validate($dto, $this->validVendor(), []);
    }

    public function testThrowsWhenItemQuantityIsZero(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('quantity must be greater than zero');

        $dto = new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-002',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO(
                    description: 'Bad item',
                    quantity: '0.0000',
                    unitPrice: 1000,
                ),
            ],
        );

        $this->validator->validate($dto, $this->validVendor(), []);
    }

    public function testThrowsWhenItemQuantityIsNegative(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('quantity must be greater than zero');

        $dto = new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-002',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO(
                    description: 'Bad item',
                    quantity: '-1.0000',
                    unitPrice: 1000,
                ),
            ],
        );

        $this->validator->validate($dto, $this->validVendor(), []);
    }

    public function testThrowsWhenItemUnitPriceIsZero(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('unit price must be greater than zero');

        $dto = new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-002',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO(
                    description: 'Free item',
                    quantity: '1.0000',
                    unitPrice: 0,
                ),
            ],
        );

        $this->validator->validate($dto, $this->validVendor(), []);
    }

    public function testItemIndexIsIncludedInErrorMessage(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Item 1:');

        $dto = new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-002',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO('Valid item', '1.0000', 1000),
                new CreateInvoiceItemDTO('Invalid item', '0.0000', 1000),
            ],
        );

        $this->validator->validate($dto, $this->validVendor(), []);
    }
}
