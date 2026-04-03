<?php 

namespace App\Repositories\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Vendor;
use App\Repositories\MySqlRepository;
use React\Promise\PromiseInterface;

class MySqlInvoiceRepository extends MySqlRepository implements InvoiceRepositoryInterface
{

    public function findAll(): array
    {
        $query = $this->withRelationsSql() . 'ORDER BY invoices.created_at DESC';

        return array_map(
            fn(array $row) => $this->hydrateRelations($row),
            $this->fetchAll($query)
        );
    }

    public function findById($id): ?Invoice
    {
        $query = $this->withRelationsSql() . 'WHERE invoices.id = ?';

        $result = $this->fetchOne($query, [$id]);

        return $result ? $this->hydrateRelations($result) : null;
    }

    public function findByIdAsync(int $id): PromiseInterface
    {
        $query = $this->withRelationsSql() . 'WHERE invoices.id = ?';

        return $this->queryAsync($query, [$id])
            ->then(
                fn($result) => $result->resultRows ? $this->hydrateRelations($result->resultRows[0]) : null
            );
    }

    public function create(array $data): Invoice
    {
        $query = '
        INSERT INTO invoices (
                vendor_id,
                invoice_status_id, 
                invoice_number, 
                invoice_date, 
                due_date, 
                amount
            )
        VALUES (?, ?, ?, ?, ?, ?)';

        $id = $this->insert(
            $query,
            [
                $data['vendor_id'],
                $data['invoice_status_id'],
                $data['invoice_number'],
                $data['invoice_date'],
                $data['due_date'],
                $data['amount'],
            ]
        );

        $invoice = $this->findById($id);

        if ($invoice === null) {
            throw new \RuntimeException("Failed to retrieve invoice after insert: $id");
        }

        return $invoice;
    }

    public function updateStatus(int $id, array $data): Invoice
    {
        $query = 'UPDATE invoices SET invoice_status_id = ? WHERE id = ?';

        $this->query(
            $query,
            [
                $data['invoice_status_id'],
                $id,
            ]
        );

        $invoice = $this->findById($id);

        if ($invoice === null) {
            throw new \RuntimeException("Failed to retrieve invoice after update: $id");
        }

        return $invoice;
    }

    public function existsByVendorAndNumber(int $vendorId, string $invoiceNumber): bool
    {
        $query = 'SELECT id FROM invoices WHERE vendor_id = ? AND invoice_number = ?';

        return $this->fetchOne(
            $query,
            [
                $vendorId,
                $invoiceNumber,
            ]
        ) !== null;
    }

    public function findPotentialDuplicates(int $vendorId, string $invoiceNumber): array
    {

        $query = $this->withRelationsSql() . 'WHERE vendor_id = ? AND invoice_number = ?';
        
        return array_map(
            fn(array $row) => $this->hydrateRelations($row),
            $this->fetchAll(
                $query,
                [
                    $vendorId,
                    $invoiceNumber,
                ]
            )
        );
    }

    public function findPotentialDuplicatesAsync(int $vendorId, string $invoiceNumber): PromiseInterface
    {
        $query = $this->withRelationsSql() . 'WHERE vendor_id = ? AND invoice_number = ?';

        return $this->queryAsync($query, [$vendorId, $invoiceNumber])
            ->then(
                fn($result) => array_map(
                    fn(array $row) => $this->hydrateRelations($row),
                    $result->resultRows
                )
            );
    }

    private function hydrateRelations(array $row): Invoice
    {
        $vendor = Vendor::fromRow([
            'id' => $row['vendor_id'],
            'name' => $row['vendor_name'],
            'email' => $row['vendor_email'],
            'created_at' => $row['vendor_created_at'],
            'updated_at' => $row['vendor_updated_at'],
        ]);

        $invoiceStatus = InvoiceStatus::fromRow([
            'id' => $row['invoice_status_id'],
            'name' => $row['invoice_status_name'],
            'slug' => $row['invoice_status_slug'],
            'description' => $row['invoice_status_description'],
            'sort_order' => $row['invoice_status_sort_order'],
            'created_at' => $row['invoice_status_created_at'],
            'updated_at' => $row['invoice_status_updated_at'],
        ]);

        return new Invoice(
            id: (int) $row['id'],
            vendorId: (int) $row['vendor_id'],
            invoiceStatusId: (int) $row['invoice_status_id'],
            invoiceNumber: (string) $row['invoice_number'],
            invoiceDate: (string) $row['invoice_date'],
            dueDate: (string) $row['due_date'],
            amount: (int) $row['amount'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
            vendor: $vendor,
            status: $invoiceStatus,
        );
    }

    private function withRelationsSql(): string 
    {
        return '
            SELECT
                invoices.*,
                vendors.id AS vendor_id,
                vendors.name AS vendor_name,
                vendors.email AS vendor_email,
                vendors.created_at AS vendor_created_at,
                vendors.updated_at AS vendor_updated_at,
                invoice_statuses.id AS invoice_status_id,
                invoice_statuses.name AS invoice_status_name,
                invoice_statuses.slug AS invoice_status_slug,
                invoice_statuses.description AS invoice_status_description,
                invoice_statuses.sort_order AS invoice_status_sort_order,
                invoice_statuses.created_at AS invoice_status_created_at,
                invoice_statuses.updated_at AS invoice_status_updated_at
            FROM invoices
            JOIN vendors ON vendors.id = invoices.vendor_id
            JOIN invoice_statuses ON invoice_statuses.id = invoices.invoice_status_id
        ';
    }
}