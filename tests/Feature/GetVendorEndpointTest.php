<?php

namespace App\Tests\Feature;

class GetVendorEndpointTest extends FeatureTestCase
{
    public function testReturns200WithVendor(): void
    {
        $response = $this->getJson('/vendors/1');

        $this->assertSame(200, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame(1, $body['id']);
        $this->assertSame('Acme Supplies Ltd', $body['name']);
        $this->assertSame('billing@acme.example', $body['email']);
    }

    public function testReturnsExpectedFields(): void
    {
        $body = $this->responseBody($this->getJson('/vendors/1'));

        $this->assertArrayHasKey('id', $body);
        $this->assertArrayHasKey('name', $body);
        $this->assertArrayHasKey('email', $body);
        $this->assertArrayHasKey('created_at', $body);
        $this->assertArrayHasKey('updated_at', $body);
    }

    public function testReturns404ForNonExistentId(): void
    {
        $response = $this->getJson('/vendors/999');

        $this->assertSame(404, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertSame('Vendor not found.', $body['error']);
    }
}
