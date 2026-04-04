<?php

namespace App\Tests\Integration\Repositories;

use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Repositories\InvoiceStatusHistory\MySqlInvoiceStatusHistoryRepository;
use App\Tests\Integration\DatabaseTestCase;

class InvoiceStatusHistoryRepositoryTest extends DatabaseTestCase
{
    private MySqlInvoiceStatusHistoryRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlInvoiceStatusHistoryRepository($this->db);
    }

    public function testFindByInvoiceIdReturnsAllHistoryEntries(): void
    {
        // Invoice 3 has 3 history entries (system, jane.smith, john.doe)
        $history = $this->repo->findByInvoiceId(3);

        $this->assertCount(3, $history);
        $this->assertContainsOnlyInstancesOf(InvoiceStatusHistory::class, $history);
    }

    public function testFindByInvoiceIdHydratesStatusRelation(): void
    {
        $history = $this->repo->findByInvoiceId(1);

        foreach ($history as $entry) {
            $this->assertInstanceOf(InvoiceStatus::class, $entry->status);
        }
    }

    public function testFindByInvoiceIdReturnsSortedByCreatedAtDescending(): void
    {
        // Invoice 2: system then jane.smith — returned in DESC order so jane.smith first
        $history = $this->repo->findByInvoiceId(2);

        $this->assertCount(2, $history);
        $this->assertSame('jane.smith', $history[0]->changedBy);
        $this->assertSame('system', $history[1]->changedBy);
    }

    public function testFindByInvoiceIdReturnsEmptyForInvoiceWithNoHistory(): void
    {
        \React\Async\await($this->db->query(
            "INSERT INTO invoices (id, vendor_id, invoice_status_id, invoice_number, invoice_date, due_date, amount)
             VALUES (99, 1, 1, 'INV-NO-HISTORY', '2024-05-01', '2024-05-31', 1000)"
        ));

        $this->assertSame([], $this->repo->findByInvoiceId(99));
    }

    public function testCreatePersistsAndReturnsHistoryEntry(): void
    {
        $entry = $this->repo->create([
            'invoice_id'        => 1,
            'invoice_status_id' => 2,
            'changed_by'        => 'test.user',
        ]);

        $this->assertInstanceOf(InvoiceStatusHistory::class, $entry);
        $this->assertSame(1, $entry->invoiceId);
        $this->assertSame(2, $entry->invoiceStatusId);
        $this->assertSame('test.user', $entry->changedBy);
        $this->assertIsInt($entry->id);
    }

    public function testCreateHydratesStatusRelation(): void
    {
        $entry = $this->repo->create([
            'invoice_id'        => 1,
            'invoice_status_id' => 2,
            'changed_by'        => 'test.user',
        ]);

        $this->assertInstanceOf(InvoiceStatus::class, $entry->status);
        $this->assertSame(2, $entry->status->id);
        $this->assertSame('approved', $entry->status->slug);
    }

    public function testCreateAppearsInFindByInvoiceId(): void
    {
        $before = count($this->repo->findByInvoiceId(1));

        $this->repo->create([
            'invoice_id'        => 1,
            'invoice_status_id' => 2,
            'changed_by'        => 'test.user',
        ]);

        $after = $this->repo->findByInvoiceId(1);

        $this->assertCount($before + 1, $after);
    }
}
