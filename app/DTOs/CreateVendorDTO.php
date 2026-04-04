<?php

namespace App\DTOs;

class CreateVendorDTO
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name: (string) ($data['name'] ?? throw new \InvalidArgumentException('name is required.')),
            email: (string) ($data['email'] ?? throw new \InvalidArgumentException('email is required.')),
        );
    }
}
