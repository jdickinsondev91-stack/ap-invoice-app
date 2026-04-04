<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class JsonRequestMiddleware
{
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface
    {
        $body = json_decode((string) $request->getBody(), true);

        if (!is_array($body)) {
            return Response::json(['error' => 'Invalid JSON body.'])->withStatus(400);
        }

        return $next($request->withParsedBody($body));
    }
}
