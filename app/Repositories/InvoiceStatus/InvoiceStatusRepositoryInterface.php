<?php 

namespace App\Repositories\InvoiceStatus;

use App\Models\InvoiceStatus;
use React\Promise\PromiseInterface;

interface InvoiceStatusRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?InvoiceStatus;

    public function findBySlug(string $slug): ?InvoiceStatus;

    public function findBySlugAsync(string $slug): PromiseInterface;
}