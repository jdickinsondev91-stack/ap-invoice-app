<?php

namespace App\Models;

class DuplicateInvoiceFlag extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly int $invoiceId,
        public readonly int $matchedInvoiceId,
        public readonly string $reason,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, [
            'id',
            'invoice_id',
            'matched_invoice_id',
            'reason',
            'created_at',
            'updated_at',
        ]);

        return new static(
            id: (int) $row['id'],
            invoiceId: (int) $row['invoice_id'],
            matchedInvoiceId: (int) $row['matched_invoice_id'],
            reason: (string) $row['reason'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoiceId,
            'matched_invoice_id' => $this->matchedInvoiceId,
            'reason' => $this->reason,
            'created_at' => $this->createdAt,
        ];
    }
}