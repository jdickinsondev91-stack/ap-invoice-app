<?php

namespace App\Models;

class Invoice extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly int $vendorId,
        public readonly int $invoiceStatusId,
        public readonly string $invoiceNumber,
        public readonly string $invoiceDate,
        public readonly string $dueDate,
        public readonly int $amount, // stored in pence, e.g. 150000 = £1500.00
        public readonly string $createdAt,
        public readonly string $updatedAt,
        public readonly ?Vendor $vendor = null,
        public readonly ?InvoiceStatus $status = null,
        /** @var InvoiceItem[] */
        public array $items = [],
        /** @var InvoiceStatusHistory[] */
        public array $history = [],
    ) {}

    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, [
            'id',
            'vendor_id',
            'invoice_status_id',
            'invoice_number',
            'invoice_date',
            'due_date',
            'amount',
            'created_at',
            'updated_at',
        ]);

        return new static(
            id: (int) $row['id'],
            vendorId: (int) $row['vendor_id'],
            invoiceStatusId: (int) $row['invoice_status_id'],
            invoiceNumber: (string) $row['invoice_number'],
            invoiceDate: (string) $row['invoice_date'],
            dueDate: (string) $row['due_date'],
            amount: (int) $row['amount'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoiceNumber,
            'invoice_date' => $this->invoiceDate,
            'due_date' => $this->dueDate,
            'amount' => $this->amount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'vendor' => $this->vendor instanceof Vendor ? $this->vendor->toArray() : null,
            'status' => $this->status instanceof InvoiceStatus ? $this->status->toArray() : null,
            'items' => $this->items ? array_map(fn(InvoiceItem $i) => $i->toArray(), $this->items) : null,
            'history' => $this->history ? array_map(fn(InvoiceStatusHistory $h) => $h->toArray(), $this->history) : null,
        ];
    }
}