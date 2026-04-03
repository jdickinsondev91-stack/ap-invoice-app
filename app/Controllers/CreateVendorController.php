<?php

namespace App\Controllers;

use App\DTOs\CreateVendorDTO;
use App\Exceptions\ValidationException;
use App\Services\VendorService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class CreateVendorController
{
    public function __construct(
        private readonly VendorService $vendorService
    ) {}

    public function __invoke(ServerRequestInterface $request): Response
    {
        $body = json_decode((string) $request->getBody(), true);

        if (!is_array($body)) {
            return Response::json(['error' => 'Invalid JSON body.'])->withStatus(400);
        }

        try {
            $vendor = $this->vendorService->create(CreateVendorDTO::fromArray($body));
        } catch (ValidationException | \InvalidArgumentException $e) {
            return Response::json(['error' => $e->getMessage()])->withStatus(422);
        }

        return Response::json($vendor->toArray())->withStatus(201);
    }
}
