<?php

namespace App\Repositories\InvoiceItem;

use App\Models\InvoiceItem;
use App\Repositories\MySqlRepository;
use React\Promise\PromiseInterface;

class MySqlInvoiceItemRepository extends MySqlRepository implements InvoiceItemRepositoryInterface
{
    
    public function findById(int $id): ?InvoiceItem
    {
       $query = 'SELECT * FROM invoice_items WHERE id = ?';

       $result = $this->fetchOne($query, [$id]);

       return $result ? InvoiceItem::fromRow($result) : null;
    }

    public function findByIdAsync(int $id): PromiseInterface
    {
        $query = 'SELECT * FROM invoice_items WHERE id = ?';

        return $this->queryAsync($query, [$id])
            ->then(
                fn($result) => $result->resultRows ? InvoiceItem::fromRow($result->resultRows[0]) : null
            );
    }

    public function findByInvoiceId(int $invoiceId): array
    {
        $query = 'SELECT * FROM invoice_items WHERE invoice_id = ?';

        return array_map(
            fn(array $row) => InvoiceItem::fromRow($row),
            $this->fetchAll($query, [$invoiceId])
        );
        
    }

    public function create(array $data): InvoiceItem
    {
        $query = '
        INSERT INTO invoice_items (
            invoice_id, 
            description, 
            quantity, 
            unit_price, 
            total
        )
        VALUES (?, ?, ?, ?, ?)';

        $id = $this->insert($query, [
            $data['invoice_id'],
            $data['description'],
            $data['quantity'],
            $data['unit_price'],
            $data['total']
        ]);

        $item = $this->findById($id);

        if ($item === null) {
            throw new \RuntimeException("Failed to retrieve invoice item after insert: $id");
        }

        return $item;
    }

    public function createAsync(array $data): PromiseInterface
    {
        $query = '
        INSERT INTO invoice_items (
            invoice_id, 
            description, 
            quantity, 
            unit_price, 
            total
        )
        VALUES (?, ?, ?, ?, ?)';

        return $this->queryAsync($query, [
            $data['invoice_id'],
            $data['description'],
            $data['quantity'],
            $data['unit_price'],
            $data['total']
        ])->then(
            fn($result) => $this->findByIdAsync($result->insertId)
        );
    }
}