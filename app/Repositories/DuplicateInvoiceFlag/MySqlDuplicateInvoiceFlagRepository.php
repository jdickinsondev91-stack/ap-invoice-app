<?php 

namespace App\Repositories\DuplicateInvoiceFlag;

use App\Models\DuplicateInvoiceFlag;
use App\Repositories\MySqlRepository;

class MySqlDuplicateInvoiceFlagRepository extends MySqlRepository implements DuplicateInvoiceFlagRepositoryInterface
{
    public function findByInvoiceId(int $invoiceId): array
    {

        $query = 'SELECT * FROM duplicate_invoice_flags WHERE invoice_id = ?';
        
        return array_map(
            fn (array $row) => DuplicateInvoiceFlag::fromRow($row),
            $this->fetchAll($query, [$invoiceId]),
        );
    }

    public function create(int $invoiceId, int $matchedInvoiceId, string $reason): DuplicateInvoiceFlag
    {
        $query = 'INSERT INTO duplicate_invoice_flags (invoice_id, matched_invoice_id, reason) VALUES (?, ?, ?)';

        $id = $this->insert(
            $query,
            [$invoiceId, $matchedInvoiceId, $reason]
        );

        $result = $this->fetchOne('SELECT * FROM duplicate_invoice_flags WHERE id = ?', [$id]);

        if ($result === null) {
            throw new \RuntimeException("Failed to retrieve duplicate invoice flag after insert: $id");
        }

        return DuplicateInvoiceFlag::fromRow($result);
    }

    public function existsForInvoicePair(int $invoiceId, int $matchedInvoiceId): bool
    {
        $query = 'SELECT id FROM duplicate_invoice_flags WHERE invoice_id = ? AND matched_invoice_id = ?';

        return $this->fetchOne(
            $query,
            [$invoiceId, $matchedInvoiceId]
        ) !== null;
    }
}
