<?php

namespace App\Tests\Integration\Repositories;

use App\Models\InvoiceStatus;
use App\Repositories\InvoiceStatus\MySqlInvoiceStatusRepository;
use App\Tests\Integration\DatabaseTestCase;

class InvoiceStatusRepositoryTest extends DatabaseTestCase
{
    private MySqlInvoiceStatusRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlInvoiceStatusRepository($this->db);
    }

    public function testFindAllReturnsAllStatuses(): void
    {
        $statuses = $this->repo->findAll();

        $this->assertCount(4, $statuses);
        $this->assertContainsOnlyInstancesOf(InvoiceStatus::class, $statuses);
    }

    public function testFindAllReturnsSortedBySortOrderAscending(): void
    {
        $statuses = $this->repo->findAll();

        $orders = array_map(fn(InvoiceStatus $s) => $s->sortOrder, $statuses);

        $this->assertSame([1, 2, 3, 4], $orders);
    }

    public function testFindByIdReturnsCorrectStatus(): void
    {
        $status = $this->repo->findById(1);

        $this->assertInstanceOf(InvoiceStatus::class, $status);
        $this->assertSame(1, $status->id);
        $this->assertSame('Pending', $status->name);
        $this->assertSame('pending', $status->slug);
    }

    public function testFindByIdReturnsNullForNonExistentId(): void
    {
        $this->assertNull($this->repo->findById(999));
    }

    public function testFindBySlugReturnsCorrectStatus(): void
    {
        $status = $this->repo->findBySlug('approved');

        $this->assertInstanceOf(InvoiceStatus::class, $status);
        $this->assertSame(2, $status->id);
        $this->assertSame('Approved', $status->name);
        $this->assertSame('approved', $status->slug);
    }

    public function testFindBySlugReturnsNullForNonExistentSlug(): void
    {
        $this->assertNull($this->repo->findBySlug('nonexistent'));
    }
}
