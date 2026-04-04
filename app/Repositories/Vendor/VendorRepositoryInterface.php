<?php 

namespace App\Repositories\Vendor;

use App\Models\Vendor;
use React\Promise\PromiseInterface;

interface VendorRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Vendor;

    public function create(array $data): Vendor;

    public function findByIdAsync(int $id): PromiseInterface;
}