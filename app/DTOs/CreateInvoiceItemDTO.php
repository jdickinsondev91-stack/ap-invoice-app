<?php

namespace App\DTOs;

class CreateInvoiceItemDTO
{
    public function __construct(
        public readonly string $description,
        public readonly string $quantity,
        public readonly int $unitPrice,
    ) {}
}
