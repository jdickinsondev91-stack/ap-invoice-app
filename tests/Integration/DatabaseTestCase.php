<?php

namespace App\Tests\Integration;

use PHPUnit\Framework\TestCase;
use React\Mysql\MysqlClient;

use function React\Async\await;

abstract class DatabaseTestCase extends TestCase
{
    protected MysqlClient $db;

    protected function setUp(): void
    {
        $this->db = new MysqlClient(
            getenv('DB_USER') . ':' . getenv('DB_PASSWORD') . '@' .
            getenv('DB_HOST') . ':' . getenv('DB_PORT') . '/' .
            getenv('DB_NAME')
        );

        foreach ($this->resetStatements() as $sql) {
            await($this->db->query($sql));
        }
    }

    private function resetStatements(): array
    {
        return [
            'SET FOREIGN_KEY_CHECKS = 0',
            'TRUNCATE TABLE duplicate_invoice_flags',
            'TRUNCATE TABLE invoice_status_history',
            'TRUNCATE TABLE invoice_items',
            'TRUNCATE TABLE invoices',
            'TRUNCATE TABLE invoice_status_transitions',
            'TRUNCATE TABLE vendors',
            'TRUNCATE TABLE invoice_statuses',
            'SET FOREIGN_KEY_CHECKS = 1',

            "INSERT INTO invoice_statuses (id, name, slug, description, sort_order) VALUES
                (1, 'Pending',  'pending',  'Awaiting review and approval', 1),
                (2, 'Approved', 'approved', 'Approved and ready for payment', 2),
                (3, 'Paid',     'paid',     'Payment has been released', 3),
                (4, 'Rejected', 'rejected', 'Invoice has been rejected', 4)",

            "INSERT INTO invoice_status_transitions (from_invoice_status_id, to_invoice_status_id) VALUES
                (1, 2), (1, 4), (2, 3), (2, 4)",

            "INSERT INTO vendors (id, name, email) VALUES
                (1, 'Acme Supplies Ltd',    'billing@acme.example'),
                (2, 'BuildRight Materials', 'accounts@buildright.example'),
                (3, 'TechParts Co',         'invoices@techparts.example')",

            "INSERT INTO invoices (id, vendor_id, invoice_status_id, invoice_number, invoice_date, due_date, amount, created_at) VALUES
                (1, 1, 1, 'INV-2024-001', '2024-03-01', '2024-03-31', 150000, '2024-03-01 10:00:00'),
                (2, 1, 2, 'INV-2024-002', '2024-03-05', '2024-04-04', 220000, '2024-03-05 10:00:00'),
                (3, 2, 3, 'INV-2024-003', '2024-02-15', '2024-03-01',  85000, '2024-03-10 10:00:00'),
                (4, 3, 4, 'INV-2024-004', '2024-03-10', '2024-04-09',  47500, '2024-03-15 10:00:00')",

            "INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, total) VALUES
                (1, 'Industrial bolts (box of 100)', 10.0000,  5000,  50000),
                (1, 'Steel brackets',                 5.0000, 20000, 100000),
                (2, 'Copper pipe 3m',                 8.0000, 15000, 120000),
                (2, 'Pipe fittings',                 20.0000,  5000, 100000),
                (3, 'Timber 4x2 (3.6m)',             10.0000,  8500,  85000),
                (4, 'Circuit board v2',               2.5000, 19000,  47500)",

            "INSERT INTO invoice_status_history (invoice_id, invoice_status_id, changed_by, created_at) VALUES
                (1, 1, 'system',     '2024-03-01 10:00:00'),
                (2, 1, 'system',     '2024-03-05 10:00:00'),
                (2, 2, 'jane.smith', '2024-03-06 10:00:00'),
                (3, 1, 'system',     '2024-02-15 10:00:00'),
                (3, 2, 'jane.smith', '2024-02-16 10:00:00'),
                (3, 3, 'john.doe',   '2024-02-17 10:00:00'),
                (4, 1, 'system',     '2024-03-10 10:00:00'),
                (4, 4, 'jane.smith', '2024-03-11 10:00:00')",
        ];
    }
}
