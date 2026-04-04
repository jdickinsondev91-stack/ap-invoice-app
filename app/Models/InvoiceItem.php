<?php

namespace App\Models;

class InvoiceItem extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly int $invoiceId,
        public readonly string $description,
        public readonly string $quantity,
        public readonly int $unitPrice,
        public readonly int $total,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, [
            'id',
            'invoice_id',
            'description',
            'quantity',
            'unit_price',
            'total',
            'created_at',
            'updated_at',
        ]);

        return new static(
            id: (int) $row['id'],
            invoiceId: (int) $row['invoice_id'],
            description: (string) $row['description'],
            quantity: (string) $row['quantity'],
            unitPrice: (int) $row['unit_price'],
            total: (int) $row['total'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total' => $this->total,
        ];
    }
}