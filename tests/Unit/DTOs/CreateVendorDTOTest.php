<?php

namespace App\Tests\Unit\DTOs;

use App\DTOs\CreateVendorDTO;
use PHPUnit\Framework\TestCase;

class CreateVendorDTOTest extends TestCase
{
    public function testFromArrayCreatesWithCorrectValues(): void
    {
        $dto = CreateVendorDTO::fromArray([
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
        ]);

        $this->assertSame('Acme Supplies Ltd', $dto->name);
        $this->assertSame('billing@acme.example', $dto->email);
    }

    public function testFromArrayThrowsForMissingName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('name is required.');

        CreateVendorDTO::fromArray(['email' => 'billing@acme.example']);
    }

    public function testFromArrayThrowsForMissingEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('email is required.');

        CreateVendorDTO::fromArray(['name' => 'Acme Supplies Ltd']);
    }
}
