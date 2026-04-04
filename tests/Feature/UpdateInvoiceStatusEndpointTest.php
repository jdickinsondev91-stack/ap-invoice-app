<?php

namespace App\Tests\Feature;

use App\Repositories\Invoice\MySqlInvoiceRepository;
use App\Repositories\InvoiceStatusHistory\MySqlInvoiceStatusHistoryRepository;

class UpdateInvoiceStatusEndpointTest extends FeatureTestCase
{
    private MySqlInvoiceRepository $invoiceRepo;
    private MySqlInvoiceStatusHistoryRepository $historyRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceRepo = new MySqlInvoiceRepository($this->db);
        $this->historyRepo = new MySqlInvoiceStatusHistoryRepository($this->db);
    }

    public function testReturns200WithUpdatedInvoice(): void
    {
        $response = $this->patchJson('/invoices/1/status', [
            'status' => 'approved',
            'changed_by' => 'jane.smith',
        ]);

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame('approved', $body['status']['slug']);
    }

    public function testPersistsStatusChangeToDatabase(): void
    {
        $this->patchJson('/invoices/1/status', [
            'status' => 'approved',
        ]);

        $invoice = $this->invoiceRepo->findById(1);

        $this->assertSame('approved', $invoice->status->slug);
    }

    public function testWritesHistoryRecord(): void
    {
        $beforeCount = count($this->historyRepo->findByInvoiceId(1));

        $this->patchJson('/invoices/1/status', [
            'status' => 'approved',
            'changed_by' => 'jane.smith',
        ]);

        $history = $this->historyRepo->findByInvoiceId(1);

        $this->assertCount($beforeCount + 1, $history);
        $this->assertSame('jane.smith', $history[0]->changedBy);
        $this->assertSame('approved', $history[0]->status->slug);
    }

    public function testDefaultsChangedByToSystem(): void
    {
        $this->patchJson('/invoices/1/status', [
            'status' => 'approved',
        ]);

        $history = $this->historyRepo->findByInvoiceId(1);

        $this->assertSame('system', $history[0]->changedBy);
    }

    public function testSupportsMultiStepTransition(): void
    {
        $this->patchJson('/invoices/1/status', ['status' => 'approved']);
        $response = $this->patchJson('/invoices/1/status', ['status' => 'paid']);

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame('paid', $body['status']['slug']);
    }

    public function testReturns422ForInvalidTransition(): void
    {
        // Invoice 3 is paid — can't go back to pending
        $response = $this->patchJson('/invoices/3/status', [
            'status' => 'pending',
        ]);

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('Cannot transition', $body['error']);
    }

    public function testReturns422ForNonExistentInvoice(): void
    {
        $response = $this->patchJson('/invoices/999/status', [
            'status' => 'approved',
        ]);

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('not found', $body['error']);
    }

    public function testReturns422ForNonExistentStatus(): void
    {
        $response = $this->patchJson('/invoices/1/status', [
            'status' => 'unknown',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testReturns422ForMissingStatus(): void
    {
        $response = $this->patchJson('/invoices/1/status', []);

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame('status is required.', $body['error']);
    }

    public function testReturns400ForInvalidJsonBody(): void
    {
        $request = new \React\Http\Message\ServerRequest('PATCH', '/invoices/1/status', [], 'not-json');

        $response = ($this->app)($request);

        $this->assertSame(400, $response->getStatusCode());
    }
}
