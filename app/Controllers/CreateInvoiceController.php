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
        $body = json_decode((string) $request->getBody(), true);

        if (!is_array($body)) {
            return Response::json(['error' => 'Invalid JSON body.'])->withStatus(400);
        }

        try {
            $invoice = $this->invoiceService->create(CreateInvoiceDTO::fromArray($body));
        } catch (ValidationException | \InvalidArgumentException $e) {
            return Response::json(['error' => $e->getMessage()])->withStatus(422);
        }

        return Response::json($invoice->toArray())->withStatus(201);
    }
}
