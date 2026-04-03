<?php

namespace App\Tests\Integration\Repositories;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Vendor;
use App\Repositories\Invoice\MySqlInvoiceRepository;
use App\Tests\Integration\DatabaseTestCase;

use function React\Async\await;

class InvoiceRepositoryTest extends DatabaseTestCase
{
    private MySqlInvoiceRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlInvoiceRepository($this->db);
    }

    public function testFindAllReturnsAllInvoices(): void
    {
        $invoices = $this->repo->findAll();

        $this->assertCount(4, $invoices);
        $this->assertContainsOnlyInstancesOf(Invoice::class, $invoices);
    }

    public function testFindAllHydratesVendorRelation(): void
    {
        $invoices = $this->repo->findAll();

        foreach ($invoices as $invoice) {
            $this->assertInstanceOf(Vendor::class, $invoice->vendor);
        }
    }

    public function testFindAllHydratesStatusRelation(): void
    {
        $invoices = $this->repo->findAll();

        foreach ($invoices as $invoice) {
            $this->assertInstanceOf(InvoiceStatus::class, $invoice->status);
        }
    }

    public function testFindAllReturnsSortedByCreatedAtDescending(): void
    {
        $invoices = $this->repo->findAll();

        $ids = array_map(fn(Invoice $i) => $i->id, $invoices);

        // Seed inserts IDs 1-4 sequentially; descending created_at order is 4,3,2,1
        $this->assertSame([4, 3, 2, 1], $ids);
    }

    public function testFindByIdReturnsCorrectInvoice(): void
    {
        $invoice = $this->repo->findById(1);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(1, $invoice->id);
        $this->assertSame(1, $invoice->vendorId);
        $this->assertSame(1, $invoice->invoiceStatusId);
        $this->assertSame('INV-2024-001', $invoice->invoiceNumber);
        $this->assertSame(150000, $invoice->amount);
    }

    public function testFindByIdHydratesVendor(): void
    {
        $invoice = $this->repo->findById(1);

        $this->assertInstanceOf(Vendor::class, $invoice->vendor);
        $this->assertSame(1, $invoice->vendor->id);
        $this->assertSame('Acme Supplies Ltd', $invoice->vendor->name);
    }

    public function testFindByIdHydratesStatus(): void
    {
        $invoice = $this->repo->findById(1);

        $this->assertInstanceOf(InvoiceStatus::class, $invoice->status);
        $this->assertSame(1, $invoice->status->id);
        $this->assertSame('pending', $invoice->status->slug);
    }

    public function testFindByIdReturnsNullForNonExistentId(): void
    {
        $this->assertNull($this->repo->findById(999));
    }

    public function testCreatePersistsAndReturnsInvoice(): void
    {
        $invoice = $this->repo->create([
            'vendor_id'         => 1,
            'invoice_status_id' => 1,
            'invoice_number'    => 'INV-2024-099',
            'invoice_date'      => '2024-04-01',
            'due_date'          => '2024-04-30',
            'amount'            => 75000,
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(1, $invoice->vendorId);
        $this->assertSame(1, $invoice->invoiceStatusId);
        $this->assertSame('INV-2024-099', $invoice->invoiceNumber);
        $this->assertSame(75000, $invoice->amount);
        $this->assertIsInt($invoice->id);
    }

    public function testCreateHydratesRelationsOnReturn(): void
    {
        $invoice = $this->repo->create([
            'vendor_id'         => 2,
            'invoice_status_id' => 1,
            'invoice_number'    => 'INV-2024-100',
            'invoice_date'      => '2024-04-01',
            'due_date'          => '2024-04-30',
            'amount'            => 30000,
        ]);

        $this->assertInstanceOf(Vendor::class, $invoice->vendor);
        $this->assertSame(2, $invoice->vendor->id);
        $this->assertInstanceOf(InvoiceStatus::class, $invoice->status);
        $this->assertSame(1, $invoice->status->id);
    }

    public function testCreatePersistsToDatabase(): void
    {
        $created = $this->repo->create([
            'vendor_id'         => 1,
            'invoice_status_id' => 1,
            'invoice_number'    => 'INV-2024-101',
            'invoice_date'      => '2024-04-01',
            'due_date'          => '2024-04-30',
            'amount'            => 10000,
        ]);

        $found = $this->repo->findById($created->id);

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
        $this->assertSame('INV-2024-101', $found->invoiceNumber);
    }

    public function testUpdateStatusChangesInvoiceStatus(): void
    {
        $invoice = $this->repo->updateStatus(1, ['invoice_status_id' => 2]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame(1, $invoice->id);
        $this->assertSame(2, $invoice->invoiceStatusId);
    }

    public function testUpdateStatusPersistsToDatabase(): void
    {
        $this->repo->updateStatus(1, ['invoice_status_id' => 2]);

        $invoice = $this->repo->findById(1);

        $this->assertSame(2, $invoice->invoiceStatusId);
        $this->assertSame('approved', $invoice->status->slug);
    }

    public function testExistsByVendorAndNumberReturnsTrueWhenExists(): void
    {
        $this->assertTrue($this->repo->existsByVendorAndNumber(1, 'INV-2024-001'));
    }

    public function testExistsByVendorAndNumberReturnsFalseWhenNotExists(): void
    {
        $this->assertFalse($this->repo->existsByVendorAndNumber(1, 'INV-DOES-NOT-EXIST'));
    }

    public function testExistsByVendorAndNumberIsScopedToVendor(): void
    {
        // INV-2024-001 exists for vendor 1 but not vendor 2
        $this->assertTrue($this->repo->existsByVendorAndNumber(1, 'INV-2024-001'));
        $this->assertFalse($this->repo->existsByVendorAndNumber(2, 'INV-2024-001'));
    }

    public function testFindPotentialDuplicatesReturnsMatchingInvoices(): void
    {
        $duplicates = $this->repo->findPotentialDuplicates(1, 'INV-2024-001');

        $this->assertCount(1, $duplicates);
        $this->assertSame(1, $duplicates[0]->id);
    }

    public function testFindPotentialDuplicatesReturnsEmptyWhenNoMatch(): void
    {
        $duplicates = $this->repo->findPotentialDuplicates(1, 'INV-DOES-NOT-EXIST');

        $this->assertSame([], $duplicates);
    }

    public function testFindPotentialDuplicatesIsScopedToVendor(): void
    {
        // INV-2024-001 belongs to vendor 1, not vendor 2
        $this->assertCount(1, $this->repo->findPotentialDuplicates(1, 'INV-2024-001'));
        $this->assertCount(0, $this->repo->findPotentialDuplicates(2, 'INV-2024-001'));
    }

    public function testFindPotentialDuplicatesAsyncReturnsMatchingInvoices(): void
    {
        $duplicates = await($this->repo->findPotentialDuplicatesAsync(1, 'INV-2024-001'));

        $this->assertCount(1, $duplicates);
        $this->assertSame(1, $duplicates[0]->id);
    }

    public function testFindPotentialDuplicatesAsyncReturnsEmptyWhenNoMatch(): void
    {
        $duplicates = await($this->repo->findPotentialDuplicatesAsync(1, 'INV-DOES-NOT-EXIST'));

        $this->assertSame([], $duplicates);
    }

    public function testFindPotentialDuplicatesAsyncIsScopedToVendor(): void
    {
        $this->assertCount(1, await($this->repo->findPotentialDuplicatesAsync(1, 'INV-2024-001')));
        $this->assertCount(0, await($this->repo->findPotentialDuplicatesAsync(2, 'INV-2024-001')));
    }
}
