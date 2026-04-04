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

    public static function fromArray(array $data): static
    {
        return new static(
            vendorId: (int) ($data['vendor_id'] ?? throw new \InvalidArgumentException('vendor_id is required.')),
            invoiceNumber: (string) ($data['invoice_number'] ?? throw new \InvalidArgumentException('invoice_number is required.')),
            invoiceDate: (string) ($data['invoice_date'] ?? throw new \InvalidArgumentException('invoice_date is required.')),
            dueDate: (string) ($data['due_date'] ?? throw new \InvalidArgumentException('due_date is required.')),
            items: array_map(
                fn(array $item) => CreateInvoiceItemDTO::fromArray($item),
                $data['items'] ?? []
            ),
        );
    }
}
