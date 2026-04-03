<?php

namespace App\Controllers;

use App\Models\InvoiceStatus;
use App\Services\InvoiceStatusService;
use React\Http\Message\Response;

class ListInvoiceStatusesController
{
    public function __construct(
        private readonly InvoiceStatusService $invoiceStatusService
    ) {}

    public function __invoke(): Response
    {
        $statuses = $this->invoiceStatusService->getAll();

        return Response::json(
            array_map(fn(InvoiceStatus $status) => $status->toArray(), $statuses)
        );
    }
}
