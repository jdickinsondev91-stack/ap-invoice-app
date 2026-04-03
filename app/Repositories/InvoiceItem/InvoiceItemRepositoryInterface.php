<?php

namespace App\Repositories\InvoiceItem;

use App\Models\InvoiceItem;
use React\Promise\PromiseInterface;

interface InvoiceItemRepositoryInterface
{
    public function findById(int $id): ?InvoiceItem;

    public function findByIdAsync(int $id): PromiseInterface;
    
    public function findByInvoiceId(int $invoiceId): array;

    public function create(array $data): InvoiceItem;

    public function createAsync(array $data): PromiseInterface;
}