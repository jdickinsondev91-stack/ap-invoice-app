<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\InvoiceStatusTransition\InvoiceStatusTransitionRepositoryInterface;
use App\Services\InvoiceStatusService;
use PHPUnit\Framework\TestCase;

use function React\Promise\resolve;

class InvoiceStatusServiceTest extends TestCase
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private InvoiceStatusRepositoryInterface $invoiceStatusRepository;
    private InvoiceStatusTransitionRepositoryInterface $invoiceStatusTransitionRepository;
    private InvoiceStatusHistoryRepositoryInterface $invoiceStatusHistoryRepository;
    private InvoiceStatusService $service;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->invoiceStatusRepository = $this->createMock(InvoiceStatusRepositoryInterface::class);
        $this->invoiceStatusTransitionRepository = $this->createMock(InvoiceStatusTransitionRepositoryInterface::class);
        $this->invoiceStatusHistoryRepository = $this->createMock(InvoiceStatusHistoryRepositoryInterface::class);

        $this->service = new InvoiceStatusService(
            $this->invoiceRepository,
            $this->invoiceStatusRepository,
            $this->invoiceStatusTransitionRepository,
            $this->invoiceStatusHistoryRepository,
        );
    }

    private function makeInvoice(int $statusId = 1, string $statusSlug = 'pending'): Invoice
    {
        $status = $this->makeStatus($statusId, $statusSlug);

        return new Invoice(
            id: 1,
            vendorId: 1,
            invoiceStatusId: $statusId,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: 150000,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            status: $status,
        );
    }

    private function makeStatus(int $id, string $slug): InvoiceStatus
    {
        return InvoiceStatus::fromRow([
            'id' => (string) $id,
            'name' => ucfirst($slug),
            'slug' => $slug,
            'description' => 'Test status',
            'sort_order' => (string) $id,
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function makeHistory(): InvoiceStatusHistory
    {
        return InvoiceStatusHistory::fromRow([
            'id' => '1',
            'invoice_id' => '1',
            'invoice_status_id' => '2',
            'changed_by' => 'system',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    public function testTransitionReturnsUpdatedInvoice(): void
    {
        $pendingInvoice = $this->makeInvoice(1, 'pending');
        $approvedStatus = $this->makeStatus(2, 'approved');
        $approvedInvoice = $this->makeInvoice(2, 'approved');

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($pendingInvoice));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($approvedStatus));
        $this->invoiceStatusTransitionRepository->method('isValidTransition')->willReturn(true);
        $this->invoiceRepository->method('updateStatus')->willReturn($approvedInvoice);
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        $result = $this->service->transition(1, 'approved');

        $this->assertSame(2, $result->invoiceStatusId);
    }

    public function testTransitionCallsUpdateStatusWithCorrectData(): void
    {
        $invoice = $this->makeInvoice(1, 'pending');
        $toStatus = $this->makeStatus(2, 'approved');

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($invoice));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($toStatus));
        $this->invoiceStatusTransitionRepository->method('isValidTransition')->willReturn(true);
        $this->invoiceRepository
            ->expects($this->once())
            ->method('updateStatus')
            ->with(1, ['invoice_status_id' => 2])
            ->willReturn($this->makeInvoice(2, 'approved'));
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        $this->service->transition(1, 'approved');
    }

    public function testTransitionWritesHistoryWithChangedBy(): void
    {
        $invoice = $this->makeInvoice(1, 'pending');
        $toStatus = $this->makeStatus(2, 'approved');

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($invoice));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($toStatus));
        $this->invoiceStatusTransitionRepository->method('isValidTransition')->willReturn(true);
        $this->invoiceRepository->method('updateStatus')->willReturn($this->makeInvoice(2, 'approved'));
        $this->invoiceStatusHistoryRepository
            ->expects($this->once())
            ->method('create')
            ->with([
                'invoice_id' => 1,
                'invoice_status_id' => 2,
                'changed_by' => 'jane.smith',
            ])
            ->willReturn($this->makeHistory());

        $this->service->transition(1, 'approved', 'jane.smith');
    }

    public function testTransitionThrowsWhenInvoiceNotFound(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Invoice with ID 999 not found.');

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve(null));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($this->makeStatus(2, 'approved')));

        $this->service->transition(999, 'approved');
    }

    public function testTransitionThrowsWhenStatusSlugNotFound(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage("Invoice status 'unknown' not found.");

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($this->makeInvoice()));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve(null));

        $this->service->transition(1, 'unknown');
    }

    public function testTransitionThrowsForInvalidTransition(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);
        $this->expectExceptionMessage("Cannot transition invoice status from 'paid' to 'pending'.");

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($this->makeInvoice(3, 'paid')));
        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($this->makeStatus(1, 'pending')));
        $this->invoiceStatusTransitionRepository->method('isValidTransition')->willReturn(false);

        $this->service->transition(1, 'pending');
    }

    public function testGetAllDelegatesToRepository(): void
    {
        $statuses = [$this->makeStatus(1, 'pending'), $this->makeStatus(2, 'approved')];

        $this->invoiceStatusRepository
            ->method('findAll')
            ->willReturn($statuses);

        $result = $this->service->getAll();

        $this->assertSame($statuses, $result);
    }

    public function testGetHistoryDelegatesToRepository(): void
    {
        $history = [$this->makeHistory()];

        $this->invoiceStatusHistoryRepository
            ->method('findByInvoiceId')
            ->with(1)
            ->willReturn($history);

        $result = $this->service->getHistory(1);

        $this->assertSame($history, $result);
    }
}
