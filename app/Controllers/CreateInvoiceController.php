<?php

namespace App\Controllers;

use App\DTOs\CreateInvoiceDTO;
use App\Exceptions\ValidationException;
use App\Services\InvoiceService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class CreateInvoiceController
{
    public function __construct(
        private readonly InvoiceService $invoiceService
    ) {}

    public function __invoke(ServerRequestInterface $request): Response
    {
        try {
            $invoice = $this->invoiceService->create(CreateInvoiceDTO::fromArray($request->getParsedBody()));
        } catch (ValidationException | \InvalidArgumentException $e) {
            return Response::json(['error' => $e->getMessage()])->withStatus(422);
        }

        return Response::json($invoice->toArray())->withStatus(201);
    }
}
