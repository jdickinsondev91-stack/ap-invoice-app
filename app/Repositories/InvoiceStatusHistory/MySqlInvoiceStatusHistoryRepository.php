<?php 

namespace App\Repositories\InvoiceStatusHistory;

use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Repositories\MySqlRepository;

class MySqlInvoiceStatusHistoryRepository extends MySqlRepository implements InvoiceStatusHistoryRepositoryInterface
{
    public function findByInvoiceId(int $invoiceId): array
    {
        $query = $this->withRelationsSql() . 'WHERE invoice_status_history.invoice_id = ? ORDER BY invoice_status_history.created_at DESC';

        return array_map(
            fn(array $row) => $this->hydrateRelations($row),
            $this->fetchAll($query, [$invoiceId])
        );
    }

    public function create(array $data): InvoiceStatusHistory
    {
        $query = '
        INSERT INTO invoice_status_history (
            invoice_id,
            invoice_status_id,
            changed_by
        )
        VALUES (?, ?, ?)';

        $id = $this->insert(
            $query,
            [
                $data['invoice_id'],
                $data['invoice_status_id'],
                $data['changed_by'],
            ]
        );

        $result = $this->fetchOne(
            $this->withRelationsSql() . 'WHERE invoice_status_history.id = ?',
            [$id]
        );

        if ($result === null) {
            throw new \RuntimeException("Failed to retrieve invoice status history after insert: $id");
        }

        return $this->hydrateRelations($result);
    }

    private function hydrateRelations(array $row): InvoiceStatusHistory
    {
        $invoiceStatus = InvoiceStatus::fromRow([
            'id' => $row['status_id'],
            'name' => $row['status_name'],
            'slug' => $row['status_slug'],
            'description' => $row['status_description'],
            'sort_order' => $row['status_sort_order'],
            'created_at' => $row['status_created_at'],
            'updated_at' => $row['status_updated_at'],
        ]);

        return new InvoiceStatusHistory(
            id: (int) $row['id'],
            invoiceId: (int) $row['invoice_id'],
            invoiceStatusId: (int) $row['invoice_status_id'],
            changedBy: (string) $row['changed_by'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
            status: $invoiceStatus,
        );
    }

    private function withRelationsSql(): string
    {
        return '
            SELECT
                invoice_status_history.*,
                invoice_statuses.id AS status_id,
                invoice_statuses.name AS status_name,
                invoice_statuses.slug AS status_slug,
                invoice_statuses.description AS status_description,
                invoice_statuses.sort_order AS status_sort_order,
                invoice_statuses.created_at AS status_created_at,
                invoice_statuses.updated_at AS status_updated_at
            FROM invoice_status_history
            JOIN invoice_statuses ON invoice_statuses.id = invoice_status_history.invoice_status_id
        ';
    }
}