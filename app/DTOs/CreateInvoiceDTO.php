<?php

namespace App\DTOs;

class CreateInvoiceDTO
{
    /**
     * @param CreateInvoiceItemDTO[] $items
     */
    public function __construct(
        public readonly int $vendorId,
        public readonly string $invoiceNumber,
        public readonly string $invoiceDate,
        public readonly string $dueDate,
        public readonly array $items,
    ) {}
}
