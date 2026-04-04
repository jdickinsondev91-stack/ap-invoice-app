<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\GetVendorController;
use App\Models\Vendor;
use App\Services\VendorService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class GetVendorControllerTest extends TestCase
{
    private VendorService $service;
    private GetVendorController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(VendorService::class);
        $this->controller = new GetVendorController($this->service);
    }

    private function makeRequest(int $id): ServerRequest
    {
        return (new ServerRequest('GET', "http://localhost/vendors/{$id}"))
            ->withAttribute('id', (string) $id);
    }

    private function makeVendor(): Vendor
    {
        return Vendor::fromRow([
            'id' => '1',
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testReturns200WithVendorWhenFound(): void
    {
        $this->service->method('getById')->with(1)->willReturn($this->makeVendor());

        $response = ($this->controller)($this->makeRequest(1));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(1, $body['id']);
        $this->assertSame('Acme Supplies Ltd', $body['name']);
    }

    public function testReturns404WhenNotFound(): void
    {
        $this->service->method('getById')->with(999)->willReturn(null);

        $response = ($this->controller)($this->makeRequest(999));

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }
}
