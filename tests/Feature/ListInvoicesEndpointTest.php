<?php

namespace App\Tests\Feature;

class ListInvoicesEndpointTest extends FeatureTestCase
{
    public function testReturns200(): void
    {
        $response = $this->getJson('/invoices');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReturnsAllInvoices(): void
    {
        $body = $this->responseBody($this->getJson('/invoices'));

        $this->assertCount(4, $body);
    }

    public function testReturnsSortedByCreatedAtDescending(): void
    {
        $body = $this->responseBody($this->getJson('/invoices'));

        $ids = array_column($body, 'id');

        $this->assertSame([4, 3, 2, 1], $ids);
    }

    public function testIncludesVendorRelation(): void
    {
        $body = $this->responseBody($this->getJson('/invoices'));

        foreach ($body as $invoice) {
            $this->assertArrayHasKey('vendor', $invoice);
            $this->assertArrayHasKey('name', $invoice['vendor']);
        }
    }

    public function testIncludesStatusRelation(): void
    {
        $body = $this->responseBody($this->getJson('/invoices'));

        foreach ($body as $invoice) {
            $this->assertArrayHasKey('status', $invoice);
            $this->assertArrayHasKey('slug', $invoice['status']);
        }
    }
}
