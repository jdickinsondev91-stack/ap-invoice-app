<?php 

namespace App\Repositories\Invoice;

use App\Models\Invoice;

interface InvoiceRepositoryInterface
{
    public function findAll(): array;

    public function findById($id): ?Invoice;

    public function create(array $data): Invoice;

    public function updateStatus(int $id, array $data): Invoice;

    public function existsByVendorAndNumber(int $vendorId, string $invoiceNumber): bool;

    public function findPotentialDuplicates(int $vendorId, string $invoiceNumber): array;
}