<?php

namespace App\Tests\Unit\Middleware;

use App\Middleware\JsonRequestMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Http\Message\ServerRequest;

class JsonRequestMiddlewareTest extends TestCase
{
    private JsonRequestMiddleware $middleware;

    protected function setUp(): void
    {
        $this->middleware = new JsonRequestMiddleware();
    }

    public function testReturns400ForInvalidJson(): void
    {
        $request = new ServerRequest('POST', '/test', [], 'not-json');

        $response = ($this->middleware)($request, fn() => Response::plaintext('ok'));

        $this->assertSame(400, $response->getStatusCode());

        $body = json_decode((string) $response->getBody(), true);

        $this->assertSame('Invalid JSON body.', $body['error']);
    }

    public function testReturns400ForEmptyBody(): void
    {
        $request = new ServerRequest('POST', '/test', [], '');

        $response = ($this->middleware)($request, fn() => Response::plaintext('ok'));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testReturns400ForJsonScalar(): void
    {
        $request = new ServerRequest('POST', '/test', [], '"just a string"');

        $response = ($this->middleware)($request, fn() => Response::plaintext('ok'));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testPassesParsedBodyToNextHandler(): void
    {
        $request = new ServerRequest('POST', '/test', [], '{"key":"value"}');
        $captured = null;

        ($this->middleware)($request, function (ServerRequestInterface $req) use (&$captured) {
            $captured = $req->getParsedBody();
            return Response::plaintext('ok');
        });

        $this->assertSame(['key' => 'value'], $captured);
    }

    public function testCallsNextHandlerForValidJson(): void
    {
        $request = new ServerRequest('POST', '/test', [], '{"key":"value"}');

        $response = ($this->middleware)($request, fn() => Response::plaintext('ok'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', (string) $response->getBody());
    }
}
