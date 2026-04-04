<?php

namespace App\Tests\Feature;

class ListVendorsEndpointTest extends FeatureTestCase
{
    public function testReturns200(): void
    {
        $response = $this->getJson('/vendors');

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testReturnsAllVendors(): void
    {
        $body = $this->responseBody($this->getJson('/vendors'));

        $this->assertCount(3, $body);
    }

    public function testReturnsSortedByName(): void
    {
        $body = $this->responseBody($this->getJson('/vendors'));

        $names = array_column($body, 'name');

        $this->assertSame(['Acme Supplies Ltd', 'BuildRight Materials', 'TechParts Co'], $names);
    }

    public function testReturnsExpectedFields(): void
    {
        $body = $this->responseBody($this->getJson('/vendors'));

        $this->assertArrayHasKey('id', $body[0]);
        $this->assertArrayHasKey('name', $body[0]);
        $this->assertArrayHasKey('email', $body[0]);
        $this->assertArrayHasKey('created_at', $body[0]);
        $this->assertArrayHasKey('updated_at', $body[0]);
    }
}
