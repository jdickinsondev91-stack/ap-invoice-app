<?php 

namespace App\Repositories\InvoiceStatus;

use App\Models\InvoiceStatus;

interface InvoiceStatusRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?InvoiceStatus;

    public function findBySlug(string $slug): ?InvoiceStatus;
}