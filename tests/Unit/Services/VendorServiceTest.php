<?php

namespace App\Tests\Unit\Services;

use App\DTOs\CreateVendorDTO;
use App\Exceptions\ValidationException;
use App\Models\Vendor;
use App\Repositories\Vendor\VendorRepositoryInterface;
use App\Services\VendorService;
use App\Validators\CreateVendorValidator;
use PHPUnit\Framework\TestCase;

class VendorServiceTest extends TestCase
{
    private VendorRepositoryInterface $vendorRepository;
    private CreateVendorValidator $validator;
    private VendorService $service;

    protected function setUp(): void
    {
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->validator = $this->createMock(CreateVendorValidator::class);
        $this->service = new VendorService($this->vendorRepository, $this->validator);
    }

    private function makeVendor(int $id = 1): Vendor
    {
        return Vendor::fromRow([
            'id' => (string) $id,
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testGetAllReturnsAllVendors(): void
    {
        $vendors = [$this->makeVendor(1), $this->makeVendor(2)];

        $this->vendorRepository->method('findAll')->willReturn($vendors);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(Vendor::class, $result);
    }

    public function testGetByIdReturnsVendorWhenFound(): void
    {
        $this->vendorRepository->method('findById')->with(1)->willReturn($this->makeVendor(1));

        $result = $this->service->getById(1);

        $this->assertInstanceOf(Vendor::class, $result);
        $this->assertSame(1, $result->id);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->vendorRepository->method('findById')->with(999)->willReturn(null);

        $this->assertNull($this->service->getById(999));
    }

    public function testCreateCallsValidator(): void
    {
        $dto = new CreateVendorDTO('Acme Supplies Ltd', 'billing@acme.example');

        $this->validator->expects($this->once())->method('validate')->with($dto);
        $this->vendorRepository->method('create')->willReturn($this->makeVendor());

        $this->service->create($dto);
    }

    public function testCreateCallsRepositoryWithCorrectData(): void
    {
        $dto = new CreateVendorDTO('Acme Supplies Ltd', 'billing@acme.example');

        $this->validator->method('validate');
        $this->vendorRepository
            ->expects($this->once())
            ->method('create')
            ->with(['name' => 'Acme Supplies Ltd', 'email' => 'billing@acme.example'])
            ->willReturn($this->makeVendor());

        $this->service->create($dto);
    }

    public function testCreateReturnsVendor(): void
    {
        $dto = new CreateVendorDTO('Acme Supplies Ltd', 'billing@acme.example');

        $this->validator->method('validate');
        $this->vendorRepository->method('create')->willReturn($this->makeVendor());

        $result = $this->service->create($dto);

        $this->assertInstanceOf(Vendor::class, $result);
        $this->assertSame('Acme Supplies Ltd', $result->name);
    }

    public function testCreateThrowsWhenValidatorFails(): void
    {
        $this->expectException(ValidationException::class);

        $dto = new CreateVendorDTO('Acme Supplies Ltd', 'bad-email');

        $this->validator->method('validate')
            ->willThrowException(new ValidationException("'bad-email' is not a valid email address."));

        $this->service->create($dto);
    }
}
