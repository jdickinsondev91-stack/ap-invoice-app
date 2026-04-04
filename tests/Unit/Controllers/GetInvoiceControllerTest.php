<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\GetInvoiceController;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class GetInvoiceControllerTest extends TestCase
{
    private InvoiceService $service;
    private GetInvoiceController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceService::class);
        $this->controller = new GetInvoiceController($this->service);
    }

    private function makeRequest(int $id): ServerRequest
    {
        return (new ServerRequest('GET', "http://localhost/invoices/{$id}"))
            ->withAttribute('id', (string) $id);
    }

    private function makeInvoice(): Invoice
    {
        return new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
        );
    }

    public function testReturns200WithInvoiceWhenFound(): void
    {
        $this->service->method('getById')->with(1)->willReturn($this->makeInvoice());

        $response = ($this->controller)($this->makeRequest(1));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(1, $body['id']);
        $this->assertSame('INV-2024-001', $body['invoice_number']);
    }

    public function testReturns404WhenNotFound(): void
    {
        $this->service->method('getById')->with(999)->willReturn(null);

        $response = ($this->controller)($this->makeRequest(999));

        $this->assertSame(404, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('error', $body);
    }

    public function testResponseIncludesItems(): void
    {
        $invoice = $this->makeInvoice();
        $invoice->items = [
            InvoiceItem::fromRow([
                'id' => '1',
                'invoice_id' => '1',
                'description' => 'Steel brackets',
                'quantity' => '5.0000',
                'unit_price' => '20000',
                'total' => '100000',
                'created_at' => '2024-03-01 09:00:00',
                'updated_at' => '2024-03-01 09:00:00',
            ]),
        ];

        $this->service->method('getById')->willReturn($invoice);

        $response = ($this->controller)($this->makeRequest(1));

        $body = json_decode((string) $response->getBody(), true);
        $this->assertCount(1, $body['items']);
        $this->assertSame('Steel brackets', $body['items'][0]['description']);
    }
}
