<?php

namespace App\Tests\Models;

use App\Exceptions\MissingRowKeyException;
use App\Models\InvoiceStatus;
use PHPUnit\Framework\TestCase;

class InvoiceStatusTest extends TestCase
{
    private function validRow(): array
    {
        return [
            'id' => '1',
            'name' => 'Pending',
            'slug' => 'pending',
            'description' => 'Awaiting review and approval',
            'sort_order' => '1',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ];
    }

    public function testFromRowHydratesCorrectly(): void
    {
        $status = InvoiceStatus::fromRow($this->validRow());

        $this->assertSame(1, $status->id);
        $this->assertSame('Pending', $status->name);
        $this->assertSame('pending', $status->slug);
        $this->assertSame('Awaiting review and approval', $status->description);
        $this->assertSame(1, $status->sortOrder);
    }

    public function testFromRowCastsSortOrderToInt(): void
    {
        $status = InvoiceStatus::fromRow($this->validRow());

        $this->assertIsInt($status->sortOrder);
    }

    public function testFromRowThrowsOnMissingKey(): void
    {
        $this->expectException(MissingRowKeyException::class);

        $row = $this->validRow();
        unset($row['slug']);

        InvoiceStatus::fromRow($row);
    }

    public function testToArrayReturnsCorrectStructure(): void
    {
        $status = InvoiceStatus::fromRow($this->validRow());

        $this->assertSame([
            'id' => 1,
            'name' => 'Pending',
            'slug' => 'pending',
            'description' => 'Awaiting review and approval',
            'sort_order' => 1,
        ], $status->toArray());
    }

    public function testToArrayExcludesTimestamps(): void
    {
        $status = InvoiceStatus::fromRow($this->validRow());
        $array = $status->toArray();

        $this->assertArrayNotHasKey('created_at', $array);
        $this->assertArrayNotHasKey('updated_at', $array);
    }
}