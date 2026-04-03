<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\ListInvoiceStatusesController;
use App\Models\InvoiceStatus;
use App\Services\InvoiceStatusService;
use PHPUnit\Framework\TestCase;

class ListInvoiceStatusesControllerTest extends TestCase
{
    private InvoiceStatusService $service;
    private ListInvoiceStatusesController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceStatusService::class);
        $this->controller = new ListInvoiceStatusesController($this->service);
    }

    private function makeStatus(int $id, string $slug): InvoiceStatus
    {
        return InvoiceStatus::fromRow([
            'id' => (string) $id,
            'name' => ucfirst($slug),
            'slug' => $slug,
            'description' => 'Test status',
            'sort_order' => (string) $id,
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testReturns200WithStatusArray(): void
    {
        $this->service->method('getAll')->willReturn([
            $this->makeStatus(1, 'pending'),
            $this->makeStatus(2, 'approved'),
            $this->makeStatus(3, 'paid'),
            $this->makeStatus(4, 'rejected'),
        ]);

        $response = ($this->controller)();

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(4, $body);
        $this->assertSame('pending', $body[0]['slug']);
        $this->assertSame('approved', $body[1]['slug']);
    }

    public function testResponseContainsExpectedFields(): void
    {
        $this->service->method('getAll')->willReturn([
            $this->makeStatus(1, 'pending'),
        ]);

        $response = ($this->controller)();
        $body = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('id', $body[0]);
        $this->assertArrayHasKey('name', $body[0]);
        $this->assertArrayHasKey('slug', $body[0]);
        $this->assertArrayHasKey('description', $body[0]);
        $this->assertArrayHasKey('sort_order', $body[0]);
    }
}
