<?php

namespace App\Repositories\DuplicateInvoiceFlag;

use App\Models\DuplicateInvoiceFlag;

interface DuplicateInvoiceFlagRepositoryInterface
{
    public function findByInvoiceId(int $invoiceId): array;

    public function create(int $invoiceId, int $matchInvoiceId, string $reason): DuplicateInvoiceFlag;

    public function existsForInvoicePair(int $invoiceId, int $matchInvoiceId): bool;
}