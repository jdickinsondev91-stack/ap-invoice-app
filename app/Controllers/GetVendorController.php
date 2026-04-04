<?php

namespace App\Controllers;

use App\Services\VendorService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class GetVendorController
{
    public function __construct(
        private readonly VendorService $vendorService
    ) {}

    public function __invoke(ServerRequestInterface $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $vendor = $this->vendorService->getById($id);

        if ($vendor === null) {
            return Response::json(['error' => 'Vendor not found.'])->withStatus(404);
        }

        return Response::json($vendor->toArray());
    }
}
