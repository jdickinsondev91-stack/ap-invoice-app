<?php 

namespace App\Tests\Unit\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\Vendor;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    public function testAssertAllKeysPresent(): void 
    {
        $row = [
            'id' => '1',
            'name' => 'Test Vendor',
            'email' => 'test-vendor@example.com',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        $vendor = Vendor::fromRow($row);

        $this->assertInstanceOf(Vendor::class, $vendor);
    }

    public function testAssertMissingKeys(): void 
    {
        $this->expectException(MissingRowKeyException::class);
        $this->expectExceptionMessage("Missing required key 'email' in row data for App\Models\Vendor");

        $row = [
            'id' => '1',
            'name' => 'Test Vendor',
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-01 00:00:00',
        ];

        Vendor::fromRow($row);
    }
}