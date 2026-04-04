<?php

namespace App\Tests\Unit\Services;

use App\DTOs\CreateInvoiceDTO;
use App\DTOs\CreateInvoiceItemDTO;
use App\Exceptions\ValidationException;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\InvoiceStatusHistory;
use App\Models\Vendor;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\InvoiceItem\InvoiceItemRepositoryInterface;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\Vendor\VendorRepositoryInterface;
use App\Services\InvoiceService;
use App\UnitOfWork\UnitOfWorkInterface;
use App\Validators\CreateInvoiceValidator;
use PHPUnit\Framework\TestCase;

use function React\Promise\resolve;

class InvoiceServiceTest extends TestCase
{
    private InvoiceRepositoryInterface $invoiceRepository;
    private InvoiceItemRepositoryInterface $invoiceItemRepository;
    private InvoiceStatusRepositoryInterface $invoiceStatusRepository;
    private InvoiceStatusHistoryRepositoryInterface $invoiceStatusHistoryRepository;
    private VendorRepositoryInterface $vendorRepository;
    private CreateInvoiceValidator $validator;
    private UnitOfWorkInterface $unitOfWork;
    private InvoiceService $service;

    protected function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepositoryInterface::class);
        $this->invoiceItemRepository = $this->createMock(InvoiceItemRepositoryInterface::class);
        $this->invoiceStatusRepository = $this->createMock(InvoiceStatusRepositoryInterface::class);
        $this->invoiceStatusHistoryRepository = $this->createMock(InvoiceStatusHistoryRepositoryInterface::class);
        $this->vendorRepository = $this->createMock(VendorRepositoryInterface::class);
        $this->validator = $this->createMock(CreateInvoiceValidator::class);

        $this->unitOfWork = $this->createMock(UnitOfWorkInterface::class);
        $this->unitOfWork->method('run')->willReturnCallback(fn(callable $cb) => $cb());

        $this->service = new InvoiceService(
            $this->invoiceRepository,
            $this->invoiceItemRepository,
            $this->invoiceStatusRepository,
            $this->invoiceStatusHistoryRepository,
            $this->vendorRepository,
            $this->validator,
            $this->unitOfWork,
        );
    }

    private function makeVendor(): Vendor
    {
        return Vendor::fromRow([
            'id' => '1',
            'name' => 'Acme Supplies Ltd',
            'email' => 'billing@acme.example',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function makeStatus(int $id = 1, string $slug = 'pending'): InvoiceStatus
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

    private function makeInvoice(int $id = 1, int $amount = 150000): Invoice
    {
        return new Invoice(
            id: $id,
            vendorId: 1,
            invoiceStatusId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            amount: $amount,
            createdAt: '2024-03-01 09:00:00',
            updatedAt: '2024-03-01 09:00:00',
            vendor: $this->makeVendor(),
            status: $this->makeStatus(),
        );
    }

    private function makeInvoiceItem(int $id = 1): InvoiceItem
    {
        return InvoiceItem::fromRow([
            'id' => (string) $id,
            'invoice_id' => '1',
            'description' => 'Industrial bolts',
            'quantity' => '10.0000',
            'unit_price' => '5000',
            'total' => '50000',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function makeHistory(): InvoiceStatusHistory
    {
        return InvoiceStatusHistory::fromRow([
            'id' => '1',
            'invoice_id' => '1',
            'invoice_status_id' => '1',
            'changed_by' => 'system',
            'created_at' => '2024-03-01 09:00:00',
            'updated_at' => '2024-03-01 09:00:00',
        ]);
    }

    private function makeCreateDTO(): CreateInvoiceDTO
    {
        return new CreateInvoiceDTO(
            vendorId: 1,
            invoiceNumber: 'INV-2024-001',
            invoiceDate: '2024-03-01',
            dueDate: '2024-03-31',
            items: [
                new CreateInvoiceItemDTO('Industrial bolts', '10', 5000),
                new CreateInvoiceItemDTO('Steel brackets', '5', 20000),
            ],
        );
    }

    // ── getById ──

    public function testGetByIdReturnsInvoiceWithItems(): void
    {
        $invoice = $this->makeInvoice();
        $items = [$this->makeInvoiceItem(1), $this->makeInvoiceItem(2)];

        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve($invoice));
        $this->invoiceItemRepository->method('findByInvoiceIdAsync')->willReturn(resolve($items));

        $result = $this->service->getById(1);

        $this->assertInstanceOf(Invoice::class, $result);
        $this->assertCount(2, $result->items);
    }

    public function testGetByIdReturnsNullWhenNotFound(): void
    {
        $this->invoiceRepository->method('findByIdAsync')->willReturn(resolve(null));
        $this->invoiceItemRepository->method('findByInvoiceIdAsync')->willReturn(resolve([]));

        $this->assertNull($this->service->getById(999));
    }

    // ── getAll ──

    public function testGetAllDelegatesToRepository(): void
    {
        $invoices = [$this->makeInvoice(1), $this->makeInvoice(2)];

        $this->invoiceRepository->method('findAll')->willReturn($invoices);

        $result = $this->service->getAll();

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(Invoice::class, $result);
    }

    // ── create ──

    public function testCreateCallsValidator(): void
    {
        $dto = $this->makeCreateDTO();
        $vendor = $this->makeVendor();
        $pendingStatus = $this->makeStatus(1, 'pending');

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($pendingStatus));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve($vendor));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with($dto, $vendor, []);

        $this->invoiceRepository->method('create')->willReturn($this->makeInvoice());
        $this->invoiceItemRepository->method('createAsync')->willReturn(resolve($this->makeInvoiceItem()));
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        $this->service->create($dto);
    }

    public function testCreatePassesCorrectTotalToRepository(): void
    {
        $dto = $this->makeCreateDTO();
        $pendingStatus = $this->makeStatus(1, 'pending');

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($pendingStatus));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve($this->makeVendor()));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));
        $this->validator->method('validate');

        // 10 x 5000 = 50000 + 5 x 20000 = 100000 = 150000
        $this->invoiceRepository
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(fn(array $data) => $data['amount'] === 150000))
            ->willReturn($this->makeInvoice(1, 150000));

        $this->invoiceItemRepository->method('createAsync')->willReturn(resolve($this->makeInvoiceItem()));
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        $this->service->create($dto);
    }

    public function testCreateWritesStatusHistoryWithSystemChangedBy(): void
    {
        $dto = $this->makeCreateDTO();
        $pendingStatus = $this->makeStatus(1, 'pending');

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($pendingStatus));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve($this->makeVendor()));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));
        $this->validator->method('validate');
        $this->invoiceRepository->method('create')->willReturn($this->makeInvoice());
        $this->invoiceItemRepository->method('createAsync')->willReturn(resolve($this->makeInvoiceItem()));

        $this->invoiceStatusHistoryRepository
            ->expects($this->once())
            ->method('create')
            ->with([
                'invoice_id' => 1,
                'invoice_status_id' => 1,
                'changed_by' => InvoiceStatusHistory::DEFAULT_CHANGED_BY,
            ])
            ->willReturn($this->makeHistory());

        $this->service->create($dto);
    }

    public function testCreateReturnsInvoiceWithItems(): void
    {
        $dto = $this->makeCreateDTO();
        $pendingStatus = $this->makeStatus(1, 'pending');

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($pendingStatus));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve($this->makeVendor()));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));
        $this->validator->method('validate');
        $this->invoiceRepository->method('create')->willReturn($this->makeInvoice());
        $this->invoiceItemRepository->method('createAsync')->willReturn(resolve($this->makeInvoiceItem()));
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        $result = $this->service->create($dto);

        $this->assertInstanceOf(Invoice::class, $result);
        $this->assertCount(2, $result->items);
    }

    public function testCreateRunsInsideUnitOfWork(): void
    {
        $dto = $this->makeCreateDTO();
        $pendingStatus = $this->makeStatus(1, 'pending');

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($pendingStatus));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve($this->makeVendor()));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));
        $this->validator->method('validate');
        $this->invoiceItemRepository->method('createAsync')->willReturn(resolve($this->makeInvoiceItem()));
        $this->invoiceStatusHistoryRepository->method('create')->willReturn($this->makeHistory());

        // Re-create with a fresh mock so we can set expects
        $unitOfWork = $this->createMock(UnitOfWorkInterface::class);
        $unitOfWork
            ->expects($this->once())
            ->method('run')
            ->willReturnCallback(function (callable $cb) {
                return $cb();
            });

        $service = new InvoiceService(
            $this->invoiceRepository,
            $this->invoiceItemRepository,
            $this->invoiceStatusRepository,
            $this->invoiceStatusHistoryRepository,
            $this->vendorRepository,
            $this->validator,
            $unitOfWork,
        );

        $this->invoiceRepository->method('create')->willReturn($this->makeInvoice());

        $service->create($dto);
    }

    public function testCreateThrowsWhenValidationFails(): void
    {
        $this->expectException(ValidationException::class);

        $dto = $this->makeCreateDTO();

        $this->invoiceStatusRepository->method('findBySlugAsync')->willReturn(resolve($this->makeStatus()));
        $this->vendorRepository->method('findByIdAsync')->willReturn(resolve(null));
        $this->invoiceRepository->method('findPotentialDuplicatesAsync')->willReturn(resolve([]));

        $this->validator->method('validate')
            ->willThrowException(new ValidationException('Vendor with ID 1 not found.'));

        $this->service->create($dto);
    }
}
