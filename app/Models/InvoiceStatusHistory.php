<?php

namespace App\Models;

class InvoiceStatusHistory extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly int $invoiceId,
        public readonly int $invoiceStatusId,
        public readonly string $changedBy,
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?InvoiceStatus $status = null,
    ) {}

    /**
     * Hydrates a bare InvoiceStatusHistory from a database row.
     * Use InvoiceRepository to load with related status.
     */
    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, [
            'id',
            'invoice_id',
            'invoice_status_id',
            'changed_by',
            'created_at',
            'updated_at',
        ]);

        return new static(
            id: (int) $row['id'],
            invoiceId: (int) $row['invoice_id'],
            invoiceStatusId: (int) $row['invoice_status_id'],
            changedBy: (string) $row['changed_by'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status instanceof InvoiceStatus ? $this->status->toArray() : null,
            'changed_by' => $this->changedBy,
            'changed_at' => $this->createdAt,
        ];
    }
}