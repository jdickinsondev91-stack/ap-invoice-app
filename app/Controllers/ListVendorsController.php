<?php

namespace App\Controllers;

use App\Models\Vendor;
use App\Services\VendorService;
use React\Http\Message\Response;

class ListVendorsController
{
    public function __construct(
        private readonly VendorService $vendorService
    ) {}

    public function __invoke(): Response
    {
        $vendors = $this->vendorService->getAll();

        return Response::json(
            array_map(fn(Vendor $vendor) => $vendor->toArray(), $vendors)
        );
    }
}
