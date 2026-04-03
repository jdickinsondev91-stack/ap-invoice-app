<?php

namespace App\Services;

use App\DTOs\CreateVendorDTO;
use App\Models\Vendor;
use App\Repositories\Vendor\VendorRepositoryInterface;
use App\Validators\CreateVendorValidator;

class VendorService
{
    public function __construct(
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly CreateVendorValidator $createVendorValidator,
    ) {}

    public function getAll(): array
    {
        return $this->vendorRepository->findAll();
    }

    public function getById(int $id): ?Vendor
    {
        return $this->vendorRepository->findById($id);
    }

    public function create(CreateVendorDTO $dto): Vendor
    {
        $this->createVendorValidator->validate($dto);

        return $this->vendorRepository->create([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);
    }
}
