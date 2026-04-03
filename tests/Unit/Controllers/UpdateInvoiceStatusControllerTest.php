<?php

namespace App\Tests\Unit\Controllers;

use App\Controllers\UpdateInvoiceStatusController;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Services\InvoiceStatusService;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;

class UpdateInvoiceStatusControllerTest extends TestCase
{
    private InvoiceStatusService $service;
    private UpdateInvoiceStatusController $controller;

    protected function setUp(): void
    {
        $this->service = $this->createMock(InvoiceStatusService::class);
        $this->controller = new UpdateInvoiceStatusController($this->service);
    }

    private function makeRequest(int $id, mixed $body): ServerRequest
    {
        return (new ServerRequest(
            'PATCH',
            "http://localhost/invoices/{$id}/status",
            ['Content-Type' => 'application/json'],
            is_string($body) ? $body : json_encode($body)
        ))->withAttribute('id', (string) $id);
    }

    private function makeInvoice(int $statusId = 2): Invoice
    {
        return new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: $statusId,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
        );
    }

    public function testReturns200WithUpdatedInvoice(): void
    {
        $this->service->method('transition')->willReturn($this->makeInvoice(2));

        $response = ($this->controller)($this->makeRequest(1, ['status' => 'approved']));

        $this->assertSame(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame(1, $body['id']);
    }

    public function testReturns400ForInvalidJson(): void
    {
        $response = ($this->controller)($this->makeRequest(1, 'not-valid-json'));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testReturns422WhenStatusFieldMissing(): void
    {
        $response = ($this->controller)($this->makeRequest(1, ['changed_by' => 'jane.smith']));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString('status', $body['error']);
    }

    public function testReturns422ForValidationException(): void
    {
        $this->service->method('transition')
            ->willThrowException(new ValidationException('Invoice with ID 999 not found.'));

        $response = ($this->controller)($this->makeRequest(999, ['status' => 'approved']));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertSame('Invoice with ID 999 not found.', $body['error']);
    }

    public function testReturns422ForInvalidStatusTransition(): void
    {
        $this->service->method('transition')
            ->willThrowException(new InvalidStatusTransitionException('paid', 'pending'));

        $response = ($this->controller)($this->makeRequest(1, ['status' => 'pending']));

        $this->assertSame(422, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertStringContainsString("from 'paid' to 'pending'", $body['error']);
    }

    public function testPassesDefaultChangedByToService(): void
    {
        $this->service
            ->expects($this->once())
            ->method('transition')
            ->with(1, 'approved', 'system')
            ->willReturn($this->makeInvoice());

        ($this->controller)($this->makeRequest(1, ['status' => 'approved']));
    }

    public function testPassesCustomChangedByToService(): void
    {
        $this->service
            ->expects($this->once())
            ->method('transition')
            ->with(1, 'approved', 'jane.smith')
            ->willReturn($this->makeInvoice());

        ($this->controller)($this->makeRequest(1, ['status' => 'approved', 'changed_by' => 'jane.smith']));
    }
}
