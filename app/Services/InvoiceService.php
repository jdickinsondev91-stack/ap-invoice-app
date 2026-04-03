<?php 

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\InvoiceItem\InvoiceItemRepositoryInterface;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\Vendor\VendorRepositoryInterface;
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
        private readonly CreateInvoiceValidator $createInvoiceValidator
    ){}

    public function create(array $data, array $items): ?Invoice
    {
        $totalAmount = 0; //TODO - Replace

        [$pendingStatus, $vendor, $potentialDuplicates] = await(all([
            $this->invoiceStatusRepository->findBySlugAsync(InvoiceStatus::STATUS_PENDING),
            $this->vendorRepository->findByIdAsync($data['vendor_id']),
            $this->invoiceRepository->findPotentialDuplicatesAsync($data['vendor_id'], $totalAmount, $data['invoice_date'])
        ]));

        $this->createInvoiceValidator->validate(
            $data,
            $items,
            $vendor,
            $potentialDuplicates
        );

        $invoice = $this->invoiceRepository->create([
            'vendor_id' => $data['vendor_id'],
            'invoice_status_id' => $pendingStatus->id,
            'invoice_number' => $data['invoice_number'],
            'invoice_date' => $data['invoice_date'],
            'due_date' => $data['due_date'],
            'amount' => $totalAmount
        ]);

        $invoiceItems = await(all( array_map(
            fn(array $item) => $this->invoiceItemRepository->createAsync([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total' => $item['quantity'] * $item['unit_price'] //Check this is correct with storing as pence
            ]), $items
        )));

        // This could be an event fired after the invoice is created but for simplicity I kept it here
        $this->invoiceStatusHistoryRepository->create([
            'invoice_id' => $invoice->id,
            'invoice_status_id' => $pendingStatus->id,
            'changed_at' => date('Y-m-d H:i:s')
        ]);

        return $invoice;
    }
}