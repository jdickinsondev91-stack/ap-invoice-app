<?php

namespace App\Tests\Feature;

use App\Repositories\Invoice\MySqlInvoiceRepository;
use App\Repositories\InvoiceItem\MySqlInvoiceItemRepository;
use App\Repositories\InvoiceStatusHistory\MySqlInvoiceStatusHistoryRepository;

class CreateInvoiceEndpointTest extends FeatureTestCase
{
    private MySqlInvoiceRepository $invoiceRepo;
    private MySqlInvoiceItemRepository $invoiceItemRepo;
    private MySqlInvoiceStatusHistoryRepository $historyRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invoiceRepo = new MySqlInvoiceRepository($this->db);
        $this->invoiceItemRepo = new MySqlInvoiceItemRepository($this->db);
        $this->historyRepo = new MySqlInvoiceStatusHistoryRepository($this->db);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'vendor_id' => 1,
            'invoice_number' => 'INV-NEW-001',
            'invoice_date' => '2024-04-01',
            'due_date' => '2024-04-30',
            'items' => [
                ['description' => 'Widget A', 'quantity' => '10', 'unit_price' => 5000],
                ['description' => 'Widget B', 'quantity' => '2.5000', 'unit_price' => 20000],
            ],
        ], $overrides);
    }

    public function testReturns201WithCreatedInvoice(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());

        $this->assertSame(201, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertArrayHasKey('id', $body);
        $this->assertSame('INV-NEW-001', $body['invoice_number']);
    }

    public function testCalculatesCorrectTotal(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $this->assertSame(100000, $body['amount']);
    }

    public function testSetsStatusToPending(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $this->assertSame('pending', $body['status']['slug']);
    }

    public function testReturnsItemsInResponse(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $this->assertCount(2, $body['items']);
        $this->assertSame('Widget A', $body['items'][0]['description']);
        $this->assertSame('Widget B', $body['items'][1]['description']);
    }

    public function testReturnsVendorInResponse(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $this->assertArrayHasKey('vendor', $body);
        $this->assertSame('Acme Supplies Ltd', $body['vendor']['name']);
    }

    public function testPersistsInvoiceToDatabase(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $invoice = $this->invoiceRepo->findById($body['id']);

        $this->assertNotNull($invoice);
        $this->assertSame('INV-NEW-001', $invoice->invoiceNumber);
        $this->assertSame(100000, $invoice->amount);
    }

    public function testPersistsItemsToDatabase(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $items = $this->invoiceItemRepo->findByInvoiceId($body['id']);

        $this->assertCount(2, $items);
    }

    public function testPersistsStatusHistoryToDatabase(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload());
        $body = $this->responseBody($response);

        $history = $this->historyRepo->findByInvoiceId($body['id']);

        $this->assertCount(1, $history);
        $this->assertSame('system', $history[0]->changedBy);
        $this->assertSame('pending', $history[0]->status->slug);
    }

    // ── Validation ──

    public function testReturns422ForNonExistentVendor(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload(['vendor_id' => 999]));

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('Vendor with ID 999 not found', $body['error']);
    }

    public function testReturns422ForDuplicateInvoiceNumber(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload([
            'invoice_number' => 'INV-2024-001',
        ]));

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('already exists', $body['error']);
    }

    public function testReturns422ForEmptyItems(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload(['items' => []]));

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('at least one item', $body['error']);
    }

    public function testReturns422ForZeroQuantity(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload([
            'items' => [
                ['description' => 'Bad item', 'quantity' => '0', 'unit_price' => 5000],
            ],
        ]));

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('quantity must be greater than zero', $body['error']);
    }

    public function testReturns422ForZeroUnitPrice(): void
    {
        $response = $this->postJson('/invoices', $this->validPayload([
            'items' => [
                ['description' => 'Bad item', 'quantity' => '1', 'unit_price' => 0],
            ],
        ]));

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertStringContainsString('unit price must be greater than zero', $body['error']);
    }

    public function testReturns422ForMissingRequiredFields(): void
    {
        $response = $this->postJson('/invoices', ['vendor_id' => 1]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testReturns400ForInvalidJsonBody(): void
    {
        $request = new \React\Http\Message\ServerRequest('POST', '/invoices', [], 'not-json');

        $response = ($this->app)($request);

        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame('Invalid JSON body.', $body['error']);
    }

    // ── Atomicity ──

    public function testDoesNotPersistInvoiceWhenValidationFails(): void
    {
        $beforeCount = count($this->invoiceRepo->findAll());

        $this->postJson('/invoices', $this->validPayload(['vendor_id' => 999]));

        $afterCount = count($this->invoiceRepo->findAll());

        $this->assertSame($beforeCount, $afterCount);
    }
}
