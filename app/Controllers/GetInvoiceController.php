<?php

namespace App\Controllers;

use App\Services\InvoiceService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class GetInvoiceController
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function __invoke(ServerRequestInterface $request): Response
    {
        $id = (int) $request->getAttribute('id');
        $invoice = $this->invoiceService->getById($id);

        if ($invoice === null) {
            return Response::json(['error' => 'Invoice not found.'])->withStatus(404);
        }

        return Response::json($invoice->toArray());
    }
}
