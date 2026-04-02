<?php

namespace App\Models;

class InvoiceStatusTransition extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly int $fromInvoiceStatusId,
        public readonly int $toInvoiceStatusId,
    ) {}

    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, ['id', 'from_invoice_status_id', 'to_invoice_status_id']);

        return new static(
            id: (int) $row['id'],
            fromInvoiceStatusId: (int) $row['from_invoice_status_id'],
            toInvoiceStatusId: (int) $row['to_invoice_status_id'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'from_invoice_status_id' => $this->fromInvoiceStatusId,
            'to_invoice_status_id' => $this->toInvoiceStatusId,
        ];
    }
}