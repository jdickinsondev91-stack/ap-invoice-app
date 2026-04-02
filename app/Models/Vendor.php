<?php

namespace App\Models;

class Vendor extends Model
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}
 
    public static function fromRow(array $row): static
    {
        self::assertRowKeys($row, ['id', 'name', 'email', 'created_at', 'updated_at']);
 
        return new static(
            id: (int) $row['id'],
            name: (string) $row['name'],
            email: (string) $row['email'],
            createdAt: (string) $row['created_at'],
            updatedAt: (string) $row['updated_at'],
        );
    }
 
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}