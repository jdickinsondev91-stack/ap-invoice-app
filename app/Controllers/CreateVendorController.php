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
        try {
            $vendor = $this->vendorService->create(CreateVendorDTO::fromArray($request->getParsedBody()));
        } catch (ValidationException | \InvalidArgumentException $e) {
            return Response::json(['error' => $e->getMessage()])->withStatus(422);
        }

        return Response::json($vendor->toArray())->withStatus(201);
    }
}
