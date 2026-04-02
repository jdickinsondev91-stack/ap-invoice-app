<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\Vendor;
use PHPUnit\Framework\TestCase;

class VendorTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $vendor = Vendor::fromRow($this->validRow());

        $this->assertSame(1, $vendor->id);
        $this->assertSame('Acme Supplies Ltd', $vendor->name);
        $this->assertSame('billing@acme.example', $vendor->email);
        $this->assertSame('2024-03-01 09:00:00', $vendor->createdAt);
        $this->assertSame('2024-03-01 09:00:00', $vendor->updatedAt);
    }

    public function testFromRowCastsIdToInt(): void
    {
        $vendor = Vendor::fromRow($this->validRow());

        $this->assertIsInt($vendor->id);
    }

    public function testFromRowCastsStringFieldsToString(): void
    {
        $vendor = Vendor::fromRow($this->validRow());

        $this->assertIsString($vendor->name);
        $this->assertIsString($vendor->email);
        $this->assertIsString($vendor->createdAt);
        $this->assertIsString($vendor->updatedAt);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['email']);

        Vendor::fromRow($row);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $vendor = Vendor::fromRow($this->validRow());

        $array = $vendor->toArray();

        $this->assertSame([
            'id' => 1,
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ], $array);
    }
}