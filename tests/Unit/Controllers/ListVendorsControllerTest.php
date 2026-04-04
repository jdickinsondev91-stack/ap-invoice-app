<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\ListVendorsController;
use App\Models\Vendor;
use App\Services\VendorService;
use PHPUnit\Framework\TestCase;

class ListVendorsControllerTest extends TestCase
{
    private VendorService $service;
    private ListVendorsController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(VendorService::class);
        $this->controller = new ListVendorsController($this->service);
    }

    private function makeVendor(int $id, string $name): Vendor
    {
        return Vendor::fromRow([
            'id' => (string) $id,
            'name' => $name,
            'email' => 'billing@example.com',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testReturns200WithVendorArray(): void
    {
        $this->service->method('getAll')->willReturn([
            $this->makeVendor(1, 'Acme Supplies Ltd'),
            $this->makeVendor(2, 'BuildRight Materials'),
        ]);

        $response = ($this->controller)();

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(2, $body);
        $this->assertSame('Acme Supplies Ltd', $body[0]['name']);
        $this->assertSame('BuildRight Materials', $body[1]['name']);
    }

    public function testReturns200WithEmptyArray(): void
    {
        $this->service->method('getAll')->willReturn([]);

        $response = ($this->controller)();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], json_decode((string) $response->getBody(), true));
    }
}
