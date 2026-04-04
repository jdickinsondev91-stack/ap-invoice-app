<?php

namespace App\Validators;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\CreateInvoiceItemDTO;
use App\Exceptions\ValidationException;
use App\Models\Vendor;

class CreateInvoiceValidator
{
    public function validate(CreateInvoiceDTO $dto, ?Vendor $vendor, array $potentialDuplicates): void
    {
        if ($vendor === null) {
            throw new ValidationException("Vendor with ID {$dto->vendorId} not found.");
        }

        if (!empty($potentialDuplicates)) {
            throw new ValidationException(
                "Invoice number '{$dto->invoiceNumber}' already exists for vendor '{$vendor->name}'."
            );
        }

        if (empty($dto->items)) {
            throw new ValidationException('An invoice must have at least one item.');
        }

        foreach ($dto->items as $index => $item) {
            $this->validateItem($item, $index);
        }
    }

    private function validateItem(CreateInvoiceItemDTO $item, int $index): void
    {
        if ((float) $item->quantity <= 0) {
            throw new ValidationException("Item {$index}: quantity must be greater than zero.");
        }

        if ($item->unitPrice <= 0) {
            throw new ValidationException("Item {$index}: unit price must be greater than zero.");
        }
    }
}
