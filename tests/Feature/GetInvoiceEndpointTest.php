<?php

namespace App\Tests\Feature;

class GetInvoiceEndpointTest extends FeatureTestCase
{
    public function testReturns200WithInvoice(): void
    {
        $response = $this->getJson('/invoices/1');

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame(1, $body['id']);
        $this->assertSame('INV-2024-001', $body['invoice_number']);
        $this->assertSame(150000, $body['amount']);
    }

    public function testIncludesVendor(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/1'));

        $this->assertArrayHasKey('vendor', $body);
        $this->assertSame('Acme Supplies Ltd', $body['vendor']['name']);
    }

    public function testIncludesStatus(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/1'));

        $this->assertArrayHasKey('status', $body);
        $this->assertSame('pending', $body['status']['slug']);
    }

    public function testIncludesItems(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/1'));

        $this->assertCount(2, $body['items']);
        $this->assertArrayHasKey('description', $body['items'][0]);
        $this->assertArrayHasKey('quantity', $body['items'][0]);
        $this->assertArrayHasKey('unit_price', $body['items'][0]);
        $this->assertArrayHasKey('total', $body['items'][0]);
    }

    public function testReturns404ForNonExistentId(): void
    {
        $response = $this->getJson('/invoices/999');

        $this->assertSame(404, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame('Invoice not found.', $body['error']);
    }
}
