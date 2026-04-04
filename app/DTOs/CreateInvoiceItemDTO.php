<?php

namespace App\DTOs;

class CreateInvoiceItemDTO
{
    public function __construct(
        public readonly string $description,
        public readonly string $quantity,
        public readonly int $unitPrice,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            description: (string) ($data['description'] ?? throw new \InvalidArgumentException('Item description is required.')),
            quantity: (string) ($data['quantity'] ?? throw new \InvalidArgumentException('Item quantity is required.')),
            unitPrice: (int) ($data['unit_price'] ?? throw new \InvalidArgumentException('Item unit_price is required.')),
        );
    }
}
