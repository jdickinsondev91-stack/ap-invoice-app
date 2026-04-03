<?php

namespace App\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\InvoiceStatusTransition\InvoiceStatusTransitionRepositoryInterface;

use function React\Async\await;
use function React\Promise\all;

class InvoiceStatusService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceStatusRepositoryInterface $invoiceStatusRepository,
        private readonly InvoiceStatusTransitionRepositoryInterface $invoiceStatusTransitionRepository,
        private readonly InvoiceStatusHistoryRepositoryInterface $invoiceStatusHistoryRepository,
    ) {}

    public function transition(int $invoiceId, string $toSlug, string $changedBy = 'system'): Invoice
    {
        /** @var array{0: ?Invoice, 1: ?InvoiceStatus} */
        [$invoice, $toStatus] = await(all([
            $this->invoiceRepository->findByIdAsync($invoiceId),
            $this->invoiceStatusRepository->findBySlugAsync($toSlug),
        ]));

        if ($invoice === null) {
            throw new ValidationException("Invoice with ID {$invoiceId} not found.");
        }

        if ($toStatus === null) {
            throw new ValidationException("Invoice status '{$toSlug}' not found.");
        }

        if (!$this->invoiceStatusTransitionRepository->isValidTransition($invoice->invoiceStatusId, $toStatus->id)) {
            throw new InvalidStatusTransitionException($invoice->status->slug, $toStatus->slug);
        }

        $invoice = $this->invoiceRepository->updateStatus($invoiceId, [
            'invoice_status_id' => $toStatus->id,
        ]);

        $this->invoiceStatusHistoryRepository->create([
            'invoice_id' => $invoiceId,
            'invoice_status_id' => $toStatus->id,
            'changed_by' => $changedBy,
        ]);

        return $invoice;
    }

    /**
     * @return InvoiceStatus[]
     */
    public function getAll(): array
    {
        return $this->invoiceStatusRepository->findAll();
    }

    /**
     * @return InvoiceStatusHistory[]
     */
    public function getHistory(int $invoiceId): array
    {
        return $this->invoiceStatusHistoryRepository->findByInvoiceId($invoiceId);
    }
}
