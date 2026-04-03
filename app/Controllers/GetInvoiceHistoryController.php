<?php

namespace App\Controllers;

use App\Models\InvoiceStatusHistory;
use App\Services\InvoiceStatusService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class GetInvoiceHistoryController
{
    public function __construct(
        private readonly InvoiceStatusService $invoiceStatusService
    ) {}

    public function __invoke(ServerRequestInterface $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $history = $this->invoiceStatusService->getHistory($id);

        return Response::json(
            array_map(fn(InvoiceStatusHistory $entry) => $entry->toArray(), $history)
        );
    }
}
