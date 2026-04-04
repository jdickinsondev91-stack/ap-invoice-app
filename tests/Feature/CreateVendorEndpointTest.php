<?php

namespace App\Tests\Feature;

use App\Repositories\Vendor\MySqlVendorRepository;

class CreateVendorEndpointTest extends FeatureTestCase
{
    private MySqlVendorRepository $vendorRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->vendorRepo = new MySqlVendorRepository($this->db);
    }

    public function testReturns201WithCreatedVendor(): void
    {
        $response = $this->postJson('/vendors', [
            'name' => 'New Vendor',
            'email' => 'new@vendor.example',
        ]);

        $this->assertSame(201, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertArrayHasKey('id', $body);
        $this->assertSame('New Vendor', $body['name']);
        $this->assertSame('new@vendor.example', $body['email']);
    }

    public function testPersistsVendorToDatabase(): void
    {
        $response = $this->postJson('/vendors', [
            'name' => 'Persisted Vendor',
            'email' => 'persist@vendor.example',
        ]);

        $body = $this->responseBody($response);
        $vendor = $this->vendorRepo->findById($body['id']);

        $this->assertNotNull($vendor);
        $this->assertSame('Persisted Vendor', $vendor->name);
    }

    public function testReturns422ForInvalidEmail(): void
    {
        $response = $this->postJson('/vendors', [
            'name' => 'Bad Vendor',
            'email' => 'not-an-email',
        ]);

        $this->assertSame(422, $response->getStatusCode());

        $body = $this->responseBody($response);

        $this->assertArrayHasKey('error', $body);
        $this->assertStringContainsString('not a valid email', $body['error']);
    }

    public function testReturns422ForMissingName(): void
    {
        $response = $this->postJson('/vendors', [
            'email' => 'valid@email.example',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testReturns422ForMissingEmail(): void
    {
        $response = $this->postJson('/vendors', [
            'name' => 'No Email Vendor',
        ]);

        $this->assertSame(422, $response->getStatusCode());
    }

    public function testReturns400ForInvalidJsonBody(): void
    {
        $request = new \React\Http\Message\ServerRequest('POST', '/vendors', [], 'not-json');

        $response = ($this->app)($request);

        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame('Invalid JSON body.', $body['error']);
    }
}
