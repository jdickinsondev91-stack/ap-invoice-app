<?php 

namespace App\Repositories\Invoice;

use App\Models\Invoice;
use React\Promise\PromiseInterface;

interface InvoiceRepositoryInterface
{
    public function findAll(): array;

    public function findById($id): ?Invoice;

    public function findByIdAsync(int $id): PromiseInterface;

    public function create(array $data): Invoice;

    public function updateStatus(int $id, array $data): Invoice;

    public function existsByVendorAndNumber(int $vendorId, string $invoiceNumber): bool;

    public function findPotentialDuplicates(int $vendorId, string $invoiceNumber): array;

    public function findPotentialDuplicatesAsync(int $vendorId, string $invoiceNumber): PromiseInterface;
}