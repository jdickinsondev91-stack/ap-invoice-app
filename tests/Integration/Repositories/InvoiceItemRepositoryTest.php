<?php

namespace App\Tests\Integration\Repositories;

use App\Models\InvoiceItem;
use App\Repositories\InvoiceItem\MySqlInvoiceItemRepository;
use App\Tests\Integration\DatabaseTestCase;

use function React\Async\await;

class InvoiceItemRepositoryTest extends DatabaseTestCase
{
    private MySqlInvoiceItemRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlInvoiceItemRepository($this->db);
    }

    public function testFindByIdReturnsCorrectItem(): void
    {
        $item = $this->repo->findById(1);

        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertSame(1, $item->id);
        $this->assertSame(1, $item->invoiceId);
        $this->assertSame('Industrial bolts (box of 100)', $item->description);
        $this->assertSame('10.0000', $item->quantity);
        $this->assertSame(5000, $item->unitPrice);
        $this->assertSame(50000, $item->total);
    }

    public function testFindByIdReturnsNullForNonExistentId(): void
    {
        $this->assertNull($this->repo->findById(999));
    }

    public function testFindByInvoiceIdReturnsAllItemsForInvoice(): void
    {
        // Invoice 1 has 2 items
        $items = $this->repo->findByInvoiceId(1);

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(InvoiceItem::class, $items);
    }

    public function testFindByInvoiceIdReturnsItemsBelongingToCorrectInvoice(): void
    {
        $items = $this->repo->findByInvoiceId(2);

        foreach ($items as $item) {
            $this->assertSame(2, $item->invoiceId);
        }
    }

    public function testFindByInvoiceIdReturnsEmptyForInvoiceWithNoItems(): void
    {
        // Insert a bare invoice with no items
        await($this->db->query(
            "INSERT INTO invoices (id, vendor_id, invoice_status_id, invoice_number, invoice_date, due_date, amount)
             VALUES (99, 1, 1, 'INV-NO-ITEMS', '2024-05-01', '2024-05-31', 1000)"
        ));

        $items = $this->repo->findByInvoiceId(99);

        $this->assertSame([], $items);
    }

    public function testCreatePersistsAndReturnsItem(): void
    {
        $item = $this->repo->create([
            'invoice_id'  => 1,
            'description' => 'Test widget',
            'quantity'    => '3.0000',
            'unit_price'  => 1000,
            'total'       => 3000,
        ]);

        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertSame(1, $item->invoiceId);
        $this->assertSame('Test widget', $item->description);
        $this->assertSame('3.0000', $item->quantity);
        $this->assertSame(1000, $item->unitPrice);
        $this->assertSame(3000, $item->total);
        $this->assertIsInt($item->id);
    }

    public function testCreatePersistsToDatabase(): void
    {
        $created = $this->repo->create([
            'invoice_id'  => 1,
            'description' => 'Persisted widget',
            'quantity'    => '1.0000',
            'unit_price'  => 500,
            'total'       => 500,
        ]);

        $found = $this->repo->findById($created->id);

        $this->assertNotNull($found);
        $this->assertSame('Persisted widget', $found->description);
    }

    public function testFindByIdAsyncReturnsCorrectItem(): void
    {
        $item = await($this->repo->findByIdAsync(1));

        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertSame(1, $item->id);
        $this->assertSame('Industrial bolts (box of 100)', $item->description);
    }

    public function testFindByIdAsyncReturnsNullForNonExistentId(): void
    {
        $item = await($this->repo->findByIdAsync(999));

        $this->assertNull($item);
    }

    public function testCreateAsyncPersistsAndReturnsItem(): void
    {
        $item = await($this->repo->createAsync([
            'invoice_id'  => 1,
            'description' => 'Async widget',
            'quantity'    => '3.0000',
            'unit_price'  => 1000,
            'total'       => 3000,
        ]));

        $this->assertInstanceOf(InvoiceItem::class, $item);
        $this->assertSame(1, $item->invoiceId);
        $this->assertSame('Async widget', $item->description);
        $this->assertSame('3.0000', $item->quantity);
        $this->assertSame(1000, $item->unitPrice);
        $this->assertSame(3000, $item->total);
        $this->assertIsInt($item->id);
    }

    public function testCreateAsyncPersistsToDatabase(): void
    {
        $created = await($this->repo->createAsync([
            'invoice_id'  => 1,
            'description' => 'Persisted async widget',
            'quantity'    => '1.0000',
            'unit_price'  => 500,
            'total'       => 500,
        ]));

        $found = $this->repo->findById($created->id);

        $this->assertNotNull($found);
        $this->assertSame('Persisted async widget', $found->description);
    }

    public function testCreateAppearsInFindByInvoiceId(): void
    {
        $before = count($this->repo->findByInvoiceId(3));

        $this->repo->create([
            'invoice_id'  => 3,
            'description' => 'Extra item',
            'quantity'    => '2.0000',
            'unit_price'  => 4250,
            'total'       => 8500,
        ]);

        $after = $this->repo->findByInvoiceId(3);

        $this->assertCount($before + 1, $after);
    }

    public function testFindByInvoiceIdAsyncReturnsAllItemsForInvoice(): void
    {
        $items = await($this->repo->findByInvoiceIdAsync(1));

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(InvoiceItem::class, $items);
    }

    public function testFindByInvoiceIdAsyncReturnsItemsBelongingToCorrectInvoice(): void
    {
        $items = await($this->repo->findByInvoiceIdAsync(2));

        foreach ($items as $item) {
            $this->assertSame(2, $item->invoiceId);
        }
    }

    public function testFindByInvoiceIdAsyncReturnsEmptyForInvoiceWithNoItems(): void
    {
        await($this->db->query(
            "INSERT INTO invoices (id, vendor_id, invoice_status_id, invoice_number, invoice_date, due_date, amount)
             VALUES (99, 1, 1, 'INV-NO-ITEMS', '2024-05-01', '2024-05-31', 1000)"
        ));

        $items = await($this->repo->findByInvoiceIdAsync(99));

        $this->assertSame([], $items);
    }
}
