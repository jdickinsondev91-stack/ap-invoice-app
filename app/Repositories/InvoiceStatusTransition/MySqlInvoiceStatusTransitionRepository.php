<?php

namespace App\Repositories\InvoiceStatusTransition;

use App\Models\InvoiceStatusTransition;
use App\Repositories\MySqlRepository;

class MySqlInvoiceStatusTransitionRepository extends MySqlRepository implements InvoiceStatusTransitionRepositoryInterface
{
    public function findTransitionsFrom(int $fromInvoiceStatusId): array
    {
        $query = 'SELECT * FROM invoice_status_transitions WHERE from_invoice_status_id = ?';

        return array_map(
            fn(array $row) => InvoiceStatusTransition::fromRow($row),
            $this->fetchAll($query, [$fromInvoiceStatusId])
        );
    }

    public function isValidTransition(int $fromInvoiceStatusId, int $toInvoiceStatusId): bool
    {
        $query = 'SELECT id FROM invoice_status_transitions WHERE from_invoice_status_id = ? AND to_invoice_status_id = ?';

        return $this->fetchOne($query, [$fromInvoiceStatusId, $toInvoiceStatusId]) !== null;
    }
}