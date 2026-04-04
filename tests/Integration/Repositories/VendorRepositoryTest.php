<?php

namespace App\Tests\Integration\Repositories;

use App\Models\Vendor;
use App\Repositories\Vendor\MySqlVendorRepository;
use App\Tests\Integration\DatabaseTestCase;

use function React\Async\await;

class VendorRepositoryTest extends DatabaseTestCase
{
    private MySqlVendorRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlVendorRepository($this->db);
    }

    public function testFindAllReturnsAllVendors(): void
    {
        $vendors = $this->repo->findAll();

        $this->assertCount(3, $vendors);
        $this->assertContainsOnlyInstancesOf(Vendor::class, $vendors);
    }

    public function testFindAllReturnsSortedByNameAscending(): void
    {
        $vendors = $this->repo->findAll();

        $names = array_map(fn(Vendor $v) => $v->name, $vendors);
        $sorted = $names;
        sort($sorted);

        $this->assertSame($sorted, $names);
    }

    public function testFindByIdReturnsCorrectVendor(): void
    {
        $vendor = $this->repo->findById(1);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertSame(1, $vendor->id);
        $this->assertSame('Acme Supplies Ltd', $vendor->name);
        $this->assertSame('billing@acme.example', $vendor->email);
    }

    public function testFindByIdReturnsNullForNonExistentId(): void
    {
        $this->assertNull($this->repo->findById(999));
    }

    public function testCreatePersistsAndReturnsVendor(): void
    {
        $vendor = $this->repo->create([
            'name'  => 'New Supplier Ltd',
            'email' => 'contact@newsupplier.example',
        ]);

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertSame('New Supplier Ltd', $vendor->name);
        $this->assertSame('contact@newsupplier.example', $vendor->email);
        $this->assertIsInt($vendor->id);
    }

    public function testFindByIdAsyncReturnsCorrectVendor(): void
    {
        $vendor = \React\Async\await($this->repo->findByIdAsync(1));

        $this->assertInstanceOf(Vendor::class, $vendor);
        $this->assertSame(1, $vendor->id);
        $this->assertSame('Acme Supplies Ltd', $vendor->name);
    }

    public function testFindByIdAsyncReturnsNullForNonExistentId(): void
    {
        $vendor = await($this->repo->findByIdAsync(999));

        $this->assertNull($vendor);
    }

    public function testCreatePersistsToDatabase(): void
    {
        $vendor = $this->repo->create([
            'name'  => 'Persisted Co',
            'email' => 'hello@persisted.example',
        ]);

        $found = $this->repo->findById($vendor->id);

        $this->assertNotNull($found);
        $this->assertSame($vendor->id, $found->id);
        $this->assertSame('Persisted Co', $found->name);
    }
}
