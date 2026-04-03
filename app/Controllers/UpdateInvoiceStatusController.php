<?php

namespace App\Controllers;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\ValidationException;
use App\Services\InvoiceStatusService;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;

class UpdateInvoiceStatusController
{
    public function __construct(
        private readonly InvoiceStatusService $invoiceStatusService
    ) {}

    /** Some justification on why I haven't used a DTO, it felt overkill for one property */
    public function __invoke(ServerRequestInterface $request): Response
    {
        $body = json_decode((string) $request->getBody(), true);

        if (!is_array($body)) {
            return Response::json(['error' => 'Invalid JSON body.'])->withStatus(400);
        }

        $toSlug = $body['status'] ?? null;

        if ($toSlug === null) {
            return Response::json(['error' => 'status is required.'])->withStatus(422);
        }

        $id = (int) $request->getAttribute('id');
        $changedBy = $body['changed_by'] ?? 'system';

        try {
            $invoice = $this->invoiceStatusService->transition($id, $toSlug, $changedBy);
        } catch (InvalidStatusTransitionException | ValidationException $e) {
            return Response::json(['error' => $e->getMessage()])->withStatus(422);
        }

        return Response::json($invoice->toArray());
    }
}
