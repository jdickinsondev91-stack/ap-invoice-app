<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\CreateVendorController;
use App\Exceptions\ValidationException;
use App\Models\Vendor;
use App\Services\VendorService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class CreateVendorControllerTest extends TestCase
{
    private VendorService $service;
    private CreateVendorController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(VendorService::class);
        $this->controller = new CreateVendorController($this->service);
    }

    private function makeRequest(array $body): ServerRequest
    {
        return (new ServerRequest(
            'POST',
            'http://localhost/vendors',
            ['Content-Type' => 'application/json'],
            json_encode($body)
        ))->withParsedBody($body);
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

    public function testReturns201WithCreatedVendor(): void
    {
        $this->service->method('create')->willReturn($this->makeVendor());

        $response = ($this->controller)($this->makeRequest([
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
        ]));

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(1, $body['id']);
        $this->assertSame('Acme Supplies Ltd', $body['name']);
    }

    public function testReturns422ForMissingName(): void
    {
        $response = ($this->controller)($this->makeRequest(['email' => 'billing@acme.example']));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString('name', $body['error']);
    }

    public function testReturns422ForMissingEmail(): void
    {
        $response = ($this->controller)($this->makeRequest(['name' => 'Acme Supplies Ltd']));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString('email', $body['error']);
    }

    public function testReturns422ForInvalidEmail(): void
    {
        $this->service->method('create')
            ->willThrowException(new ValidationException("'bad-email' is not a valid email address."));

        $response = ($this->controller)($this->makeRequest([
            'name' => 'Acme Supplies Ltd',
            'email' => 'bad-email',
        ]));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString('bad-email', $body['error']);
    }
}
