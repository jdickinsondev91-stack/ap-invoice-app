<?php 

namespace App\Repositories\Vendor;

use App\Models\Vendor;

interface VendorRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Vendor;

    public function create(array $data): Vendor;
}