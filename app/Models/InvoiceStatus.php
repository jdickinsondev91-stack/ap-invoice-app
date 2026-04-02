<?php 

namespace App\Models;

class InvoiceStatus extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $description,
        public readonly int $sortOrder,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}
 
    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, ['id', 'name', 'slug', 'description', 'sort_order', 'created_at', 'updated_at']);
 
        return new static(
            id: (int) $row['id'],
            name: (string) $row['name'],
            slug: (string) $row['slug'],
            description: (string) $row['description'],
            sortOrder: (int) $row['sort_order'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }
 
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'sort_order' => $this->sortOrder,
        ];
    }
}