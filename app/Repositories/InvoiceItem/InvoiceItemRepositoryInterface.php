<?php

namespace App\Repositories\InvoiceItem;

use App\Models\InvoiceItem;

interface InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem;
    
    public function findByInvoiceId(int $invoiceId): array;

    public function create(array $data): InvoiceItem;
}