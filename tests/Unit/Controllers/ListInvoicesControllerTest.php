<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\ListInvoicesController;
use App\Models\Invoice;
use App\Services\InvoiceService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class ListInvoicesControllerTest extends TestCase
{
    private InvoiceService $service;
    private ListInvoicesController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceService::class);
        $this->controller = new ListInvoicesController($this->service);
    }

    private function makeInvoice(int $id, string $invoiceNumber): Invoice
    {
        return new Invoice(
            id: $id,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: $invoiceNumber,
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
        );
    }

    public function testReturns200WithInvoiceArray(): void
    {
        $this->service->method('getAll')->willReturn([
            $this->makeInvoice(1, 'INV-2024-001'),
            $this->makeInvoice(2, 'INV-2024-002'),
        ]);

        $response = ($this->controller)();

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(2, $body);
        $this->assertSame('INV-2024-001', $body[0]['invoice_number']);
        $this->assertSame('INV-2024-002', $body[1]['invoice_number']);
    }

    public function testReturns200WithEmptyArray(): void
    {
        $this->service->method('getAll')->willReturn([]);

        $response = ($this->controller)();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], json_decode((string) $response->getBody(), true));
    }
}
