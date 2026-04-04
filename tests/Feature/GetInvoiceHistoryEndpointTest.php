<?php

namespace App\Tests\Feature;

class GetInvoiceHistoryEndpointTest extends FeatureTestCase
{
    public function testReturns200WithHistory(): void
    {
        $response = $this->getJson('/invoices/2/history');

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertCount(2, $body);
    }

    public function testReturnsExpectedFields(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/2/history'));

        $this->assertArrayHasKey('status', $body[0]);
        $this->assertArrayHasKey('changed_by', $body[0]);
        $this->assertArrayHasKey('changed_at', $body[0]);
    }

    public function testReturnsStatusDetailsInHistory(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/2/history'));

        $this->assertArrayHasKey('slug', $body[0]['status']);
        $this->assertArrayHasKey('name', $body[0]['status']);
    }

    public function testReturnsMostRecentFirst(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/2/history'));

        $this->assertSame('approved', $body[0]['status']['slug']);
        $this->assertSame('pending', $body[1]['status']['slug']);
    }

    public function testReturnsFullHistoryForMultiTransitionInvoice(): void
    {
        $body = $this->responseBody($this->getJson('/invoices/3/history'));

        $this->assertCount(3, $body);
        $this->assertSame('paid', $body[0]['status']['slug']);
        $this->assertSame('approved', $body[1]['status']['slug']);
        $this->assertSame('pending', $body[2]['status']['slug']);
    }

    public function testReturnsEmptyArrayForNonExistentInvoice(): void
    {
        $response = $this->getJson('/invoices/999/history');

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame([], $body);
    }
}
