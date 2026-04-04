<?php 

namespace App\Repositories\InvoiceStatus;

use App\Models\InvoiceStatus;
use App\Repositories\MySqlRepository;
use React\Promise\PromiseInterface;

class MySqlInvoiceStatusRepository extends MySqlRepository implements InvoiceStatusRepositoryInterface
{
    public function findAll(): array
    {
        $query = 'SELECT * FROM invoice_statuses ORDER BY sort_order ASC';

        return array_map(
            fn(array $row) => InvoiceStatus::fromRow($row),
            $this->fetchAll($query, [])
        );
    }

    public function findById(int $id): ?InvoiceStatus
    {
        $query = 'SELECT * FROM invoice_statuses WHERE id = ?';

        $row = $this->fetchOne($query, [$id]);

        return $row ? InvoiceStatus::fromRow($row) : null;
    }

    public function findBySlug(string $slug): ?InvoiceStatus
    {
        $query = 'SELECT * FROM invoice_statuses WHERE slug = ?';

        $row = $this->fetchOne($query, [$slug]);

        return $row ? InvoiceStatus::fromRow($row) : null;
    }

    public function findBySlugAsync(string $slug): PromiseInterface
    {
        $query = 'SELECT * FROM invoice_statuses WHERE slug = ?';

        return $this->queryAsync($query, [$slug])
                    ->then(
                        fn($result) => isset($result->resultRows[0]) ? InvoiceStatus::fromRow($result->resultRows[0]) : null
                    );
    }
}