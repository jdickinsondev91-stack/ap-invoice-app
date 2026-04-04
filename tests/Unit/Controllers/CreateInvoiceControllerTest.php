<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\CreateInvoiceController;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Services\InvoiceService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class CreateInvoiceControllerTest extends TestCase
{
    private InvoiceService $service;
    private CreateInvoiceController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceService::class);
        $this->controller = new CreateInvoiceController($this->service);
    }

    private function makeRequest(array $body): ServerRequest
    {
        return (new ServerRequest(
            'POST',
            'http://localhost/invoices',
            ['Content-Type' => 'application/json'],
            json_encode($body)
        ))->withParsedBody($body);
    }

    private function validBody(): array
    {
        return [
            'vendor_id' => 1,
            'invoice_number' => 'INV-2024-005',
            'invoice_date' => '2024-04-01',
            'due_date' => '2024-04-30',
            'items' => [
                [
                    'description' => 'Steel brackets',
                    'quantity' => '5.0000',
                    'unit_price' => 20000,
                ],
            ],
        ];
    }

    private function makeInvoice(): Invoice
    {
        return new Invoice(
            id: 5,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-005',
            invoiceDate: '2024-04-01',
            dueDate: '2024-04-30',
            amount: 100000,
            createdAt: '2024-04-01 09:00:00',
            updatedAt: '2024-04-01 09:00:00',
        );
    }

    public function testReturns201WithCreatedInvoice(): void
    {
        $this->service->method('create')->willReturn($this->makeInvoice());

        $response = ($this->controller)($this->makeRequest($this->validBody()));

        $this->assertSame(201, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(5, $body['id']);
        $this->assertSame('INV-2024-005', $body['invoice_number']);
    }

    public function testReturns422ForValidationException(): void
    {
        $this->service->method('create')
            ->willThrowException(new ValidationException('Duplicate invoice number.'));

        $response = ($this->controller)($this->makeRequest($this->validBody()));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('Duplicate invoice number.', $body['error']);
    }

    public function testReturns422ForMissingVendorId(): void
    {
        $body = $this->validBody();
        unset($body['vendor_id']);

        $response = ($this->controller)($this->makeRequest($body));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString('vendor_id', $body['error']);
    }

    public function testReturns422ForMissingInvoiceNumber(): void
    {
        $body = $this->validBody();
        unset($body['invoice_number']);

        $response = ($this->controller)($this->makeRequest($body));

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testReturns422ForMissingItemDescription(): void
    {
        $body = $this->validBody();
        unset($body['items'][0]['description']);

        $response = ($this->controller)($this->makeRequest($body));

        $this->assertSame(422, $response->getStatusCode());
    }
}
