<?php

namespace App\Tests\Integration\Repositories;

use App\Models\DuplicateInvoiceFlag;
use App\Repositories\DuplicateInvoiceFlag\MySqlDuplicateInvoiceFlagRepository;
use App\Tests\Integration\DatabaseTestCase;

class DuplicateInvoiceFlagRepositoryTest extends DatabaseTestCase
{
    private MySqlDuplicateInvoiceFlagRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlDuplicateInvoiceFlagRepository($this->db);
    }

    public function testFindByInvoiceIdReturnsEmptyWhenNoFlags(): void
    {
        $this->assertSame([], $this->repo->findByInvoiceId(1));
    }

    public function testFindByInvoiceIdReturnsCreatedFlags(): void
    {
        $this->repo->create(1, 2, 'Same invoice number');
        $this->repo->create(1, 3, 'Same amount');

        $flags = $this->repo->findByInvoiceId(1);

        $this->assertCount(2, $flags);
        $this->assertContainsOnlyInstancesOf(DuplicateInvoiceFlag::class, $flags);
    }

    public function testFindByInvoiceIdReturnsFlagsForCorrectInvoice(): void
    {
        $this->repo->create(1, 2, 'Possible duplicate');
        $this->repo->create(3, 4, 'Another duplicate');

        $flags = $this->repo->findByInvoiceId(1);

        $this->assertCount(1, $flags);
        $this->assertSame(1, $flags[0]->invoiceId);
    }

    public function testCreatePersistsAndReturnsFlag(): void
    {
        $flag = $this->repo->create(1, 2, 'Same invoice number');

        $this->assertInstanceOf(DuplicateInvoiceFlag::class, $flag);
        $this->assertSame(1, $flag->invoiceId);
        $this->assertSame(2, $flag->matchedInvoiceId);
        $this->assertSame('Same invoice number', $flag->reason);
        $this->assertIsInt($flag->id);
    }

    public function testCreatePersistsToDatabase(): void
    {
        $this->repo->create(2, 3, 'Matching amount and vendor');

        $flags = $this->repo->findByInvoiceId(2);

        $this->assertCount(1, $flags);
        $this->assertSame(3, $flags[0]->matchedInvoiceId);
        $this->assertSame('Matching amount and vendor', $flags[0]->reason);
    }

    public function testExistsForInvoicePairReturnsTrueWhenFlagExists(): void
    {
        $this->repo->create(1, 2, 'Duplicate');

        $this->assertTrue($this->repo->existsForInvoicePair(1, 2));
    }

    public function testExistsForInvoicePairReturnsFalseWhenNoFlag(): void
    {
        $this->assertFalse($this->repo->existsForInvoicePair(1, 2));
    }

    public function testExistsForInvoicePairIsDirectional(): void
    {
        $this->repo->create(1, 2, 'Duplicate');

        $this->assertTrue($this->repo->existsForInvoicePair(1, 2));
        $this->assertFalse($this->repo->existsForInvoicePair(2, 1));
    }
}
