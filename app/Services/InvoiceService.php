<?php

namespace App\Services;

use App\DTOs\CreateInvoiceDTO;
use App\Helpers\MoneyHelper;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Models\Vendor;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\InvoiceItem\InvoiceItemRepositoryInterface;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\Vendor\VendorRepositoryInterface;
use App\UnitOfWork\UnitOfWorkInterface;
use App\Validators\CreateInvoiceValidator;

use function React\Async\await;
use function React\Promise\all;

class InvoiceService
{
    public function __construct(
        private readonly InvoiceRepositoryInterface $invoiceRepository,
        private readonly InvoiceItemRepositoryInterface $invoiceItemRepository,
        private readonly InvoiceStatusRepositoryInterface $invoiceStatusRepository,
        private readonly InvoiceStatusHistoryRepositoryInterface $invoiceStatusHistoryRepository,
        private readonly VendorRepositoryInterface $vendorRepository,
        private readonly CreateInvoiceValidator $createInvoiceValidator,
        private readonly UnitOfWorkInterface $unitOfWork,
    ) {}

    public function getById(int $id): ?Invoice
    {
        [$invoice, $items] = await(all([
            $this->invoiceRepository->findByIdAsync($id),
            $this->invoiceItemRepository->findByInvoiceIdAsync($id),
        ]));

        /** @var ?Invoice $invoice */                                                                                                                                                                                                               
        /** @var InvoiceItem[] $items */

        if ($invoice === null) {
            return null;
        }

        $invoice->items = $items;

        return $invoice;
    }

    public function getAll(): array
    {
        return $this->invoiceRepository->findAll();
    }

    public function create(CreateInvoiceDTO $dto): Invoice
    {
        [$pendingStatus, $vendor, $potentialDuplicates] = await(all([
            $this->invoiceStatusRepository->findBySlugAsync(InvoiceStatus::STATUS_PENDING),
            $this->vendorRepository->findByIdAsync($dto->vendorId),
            $this->invoiceRepository->findPotentialDuplicatesAsync($dto->vendorId, $dto->invoiceNumber),
        ]));

        /** @var InvoiceStatus $pendingStatus */
        /** @var ?Vendor $vendor */
        /** @var Invoice[] $potentialDuplicates */

        $this->createInvoiceValidator->validate($dto, $vendor, $potentialDuplicates);

        $totalAmount = array_sum(array_map(
            fn($item) => MoneyHelper::multiply($item->unitPrice, $item->quantity),
            $dto->items
        ));

        $invoice = $this->unitOfWork->run(function() use ($dto, $pendingStatus, $totalAmount) {

            $invoice = $this->invoiceRepository->create([
                'vendor_id' => $dto->vendorId,
                'invoice_status_id' => $pendingStatus->id,
                'invoice_number' => $dto->invoiceNumber,
                'invoice_date' => $dto->invoiceDate,
                'due_date' => $dto->dueDate,
                'amount' => $totalAmount,
            ]);

            $invoice->items = await(all(array_map(
                fn($item) => $this->invoiceItemRepository->createAsync([
                    'invoice_id' => $invoice->id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unitPrice,
                    'total' => (int) round((float) $item->quantity * $item->unitPrice),
                ]),
                $dto->items
            )));

            // This could be an event fired after the invoice is created but for simplicity I kept it here
            $this->invoiceStatusHistoryRepository->create([
                'invoice_id' => $invoice->id,
                'invoice_status_id' => $pendingStatus->id,
                'changed_by' => InvoiceStatusHistory::DEFAULT_CHANGED_BY,
            ]);

            return $invoice;
        });

        return $invoice;
    }
}
