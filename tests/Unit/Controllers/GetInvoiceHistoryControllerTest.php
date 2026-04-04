<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\GetInvoiceHistoryController;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Services\InvoiceStatusService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class GetInvoiceHistoryControllerTest extends TestCase
{
    private InvoiceStatusService $service;
    private GetInvoiceHistoryController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceStatusService::class);
        $this->controller = new GetInvoiceHistoryController($this->service);
    }

    private function makeRequest(int $id): ServerRequest
    {
        return (new ServerRequest('GET', "http://localhost/invoices/{$id}/history"))
            ->withAttribute('id', (string) $id);
    }

    private function makeHistoryEntry(string $slug, string $changedBy): InvoiceStatusHistory
    {
        $status = InvoiceStatus::fromRow([
            'id' => '1',
            'name' => ucfirst($slug),
            'slug' => $slug,
            'description' => 'Test status',
            'sort_order' => '1',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);

        return new InvoiceStatusHistory(
            id: 1,
            invoiceId: 1,
            invoiceStatusId: 1,
            changedBy: $changedBy,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $status,
        );
    }

    public function testReturns200WithHistoryArray(): void
    {
        $this->service->method('getHistory')->with(1)->willReturn([
            $this->makeHistoryEntry('pending', 'system'),
            $this->makeHistoryEntry('approved', 'jane.smith'),
        ]);

        $response = ($this->controller)($this->makeRequest(1));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(2, $body);
        $this->assertSame('system', $body[0]['changed_by']);
        $this->assertSame('jane.smith', $body[1]['changed_by']);
    }

    public function testReturns200WithEmptyArrayForInvoiceWithNoHistory(): void
    {
        $this->service->method('getHistory')->with(1)->willReturn([]);

        $response = ($this->controller)($this->makeRequest(1));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], json_decode((string) $response->getBody(), true));
    }

    public function testResponseIncludesStatusDetails(): void
    {
        $this->service->method('getHistory')->willReturn([
            $this->makeHistoryEntry('approved', 'jane.smith'),
        ]);

        $response = ($this->controller)($this->makeRequest(1));
        $body = json_decode((string) $response->getBody(), true);

        $this->assertArrayHasKey('status', $body[0]);
        $this->assertSame('approved', $body[0]['status']['slug']);
    }

    public function testPassesCorrectInvoiceIdToService(): void
    {
        $this->service
            ->expects($this->once())
            ->method('getHistory')
            ->with(42)
            ->willReturn([]);

        ($this->controller)($this->makeRequest(42));
    }
}
