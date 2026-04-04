<?php

namespace App\Tests\Feature;

use App\Tests\Integration\DatabaseTestCase;
use FrameworkX\App;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ServerRequest;

abstract class FeatureTestCase extends DatabaseTestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();

        $createContainer = require __DIR__ . '/../../bootstrap/container.php';
        $registerRoutes = require __DIR__ . '/../../bootstrap/routes.php';

        $this->app = new App($createContainer($this->db));
        $registerRoutes($this->app);
    }

    protected function request(string $method, string $uri, ?array $body = null): ResponseInterface
    {
        $headers = [];
        $requestBody = '';

        if ($body !== null) {
            $headers['Content-Type'] = 'application/json';
            $requestBody = json_encode($body);
        }

        $request = new ServerRequest($method, $uri, $headers, $requestBody);

        return ($this->app)($request);
    }

    protected function getJson(string $uri): ResponseInterface
    {
        return $this->request('GET', $uri);
    }

    protected function postJson(string $uri, array $body): ResponseInterface
    {
        return $this->request('POST', $uri, $body);
    }

    protected function patchJson(string $uri, array $body): ResponseInterface
    {
        return $this->request('PATCH', $uri, $body);
    }

    protected function responseBody(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true);
    }
}
