<?php 

namespace App\Repositories\InvoiceStatusHistory;

use App\Models\InvoiceStatusHistory;

interface InvoiceStatusHistoryRepositoryInterface
{
    public function findByInvoiceId(int $invoiceId): array;

    public function create(array $data): InvoiceStatusHistory;
}