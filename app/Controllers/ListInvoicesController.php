<?php

namespace App\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use React\Http\Message\Response;

class ListInvoicesController
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function __invoke(): Response
    {
        $invoices = $this->invoiceService->getAll();

        return Response::json(
            array_map(fn(Invoice $invoice) => $invoice->toArray(), $invoices)
        );
    }
}
