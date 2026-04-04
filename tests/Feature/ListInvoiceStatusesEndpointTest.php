<?php

namespace App\Tests\Feature;

class ListInvoiceStatusesEndpointTest extends FeatureTestCase
{
    public function testReturns200(): void
    {
        $response = $this->getJson('/invoice-statuses');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReturnsAllStatuses(): void
    {
        $body = $this->responseBody($this->getJson('/invoice-statuses'));

        $this->assertCount(4, $body);

        $slugs = array_column($body, 'slug');

        $this->assertContains('pending', $slugs);
        $this->assertContains('approved', $slugs);
        $this->assertContains('paid', $slugs);
        $this->assertContains('rejected', $slugs);
    }

    public function testReturnsSortedBySortOrder(): void
    {
        $body = $this->responseBody($this->getJson('/invoice-statuses'));

        $sortOrders = array_column($body, 'sort_order');

        $this->assertSame([1, 2, 3, 4], $sortOrders);
    }

    public function testReturnsExpectedFields(): void
    {
        $body = $this->responseBody($this->getJson('/invoice-statuses'));

        $this->assertArrayHasKey('id', $body[0]);
        $this->assertArrayHasKey('name', $body[0]);
        $this->assertArrayHasKey('slug', $body[0]);
        $this->assertArrayHasKey('description', $body[0]);
        $this->assertArrayHasKey('sort_order', $body[0]);
    }
}
