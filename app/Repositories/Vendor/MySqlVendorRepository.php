<?php 

namespace App\Repositories\Vendor;

use App\Models\Vendor;
use App\Repositories\MySqlRepository;
use React\Promise\PromiseInterface;

class MySqlVendorRepository extends MySqlRepository implements VendorRepositoryInterface
{
    public function findAll(): array
    {
        $query = 'SELECT * FROM vendors ORDER BY name ASC';

        return array_map(
            fn(array $row) => Vendor::fromRow($row),
            $this->fetchAll($query)
        );
    }

    public function findById(int $id): ?Vendor
    {
        $query = 'SELECT * FROM vendors WHERE id = ?';

        $result = $this->fetchOne($query, [$id]);

        return $result ? Vendor::fromRow($result) : null;
    }

    public function create(array $data): Vendor
    {
        $query = 'INSERT INTO vendors (name, email) VALUES (?, ?)';

        $id = $this->insert(
            $query,
            [
                $data['name'],
                $data['email'],
            ]
        );

        $vendor = $this->findById($id);

        if ($vendor === null) {
            throw new \RuntimeException("Failed to retrieve vendor after insert: $id");
        }

        return $vendor;
    }

    public function findByIdAsync(int $id): PromiseInterface
    {
        $query = 'SELECT * FROM vendors WHERE id = ?';

        return $this->queryAsync($query, [$id])
            ->then(
                fn($result) => isset($result->resultRows[0]) ? Vendor::fromRow($result->resultRows[0]) : null
            );
    }
}