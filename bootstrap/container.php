<?php

use App\Repositories\DuplicateInvoiceFlag\DuplicateInvoiceFlagRepositoryInterface;
use App\Repositories\DuplicateInvoiceFlag\MySqlDuplicateInvoiceFlagRepository;
use App\Repositories\Invoice\InvoiceRepositoryInterface;
use App\Repositories\Invoice\MySqlInvoiceRepository;
use App\Repositories\InvoiceItem\InvoiceItemRepositoryInterface;
use App\Repositories\InvoiceItem\MySqlInvoiceItemRepository;
use App\Repositories\InvoiceStatus\InvoiceStatusRepositoryInterface;
use App\Repositories\InvoiceStatus\MySqlInvoiceStatusRepository;
use App\Repositories\InvoiceStatusHistory\InvoiceStatusHistoryRepositoryInterface;
use App\Repositories\InvoiceStatusHistory\MySqlInvoiceStatusHistoryRepository;
use App\Repositories\InvoiceStatusTransition\InvoiceStatusTransitionRepositoryInterface;
use App\Repositories\InvoiceStatusTransition\MySqlInvoiceStatusTransitionRepository;
use App\Repositories\Vendor\MySqlVendorRepository;
use App\Repositories\Vendor\VendorRepositoryInterface;
use App\UnitOfWork\MySqlUnitOfWork;
use App\UnitOfWork\UnitOfWorkInterface;
use FrameworkX\Container;
use React\Mysql\MysqlClient;

return function (MysqlClient $db): Container {
    return new Container([
        MysqlClient::class => fn() => $db,
        DuplicateInvoiceFlagRepositoryInterface::class => MySqlDuplicateInvoiceFlagRepository::class,
        InvoiceRepositoryInterface::class => MySqlInvoiceRepository::class,
        InvoiceItemRepositoryInterface::class => MySqlInvoiceItemRepository::class,
        InvoiceStatusRepositoryInterface::class => MySqlInvoiceStatusRepository::class,
        InvoiceStatusHistoryRepositoryInterface::class => MySqlInvoiceStatusHistoryRepository::class,
        InvoiceStatusTransitionRepositoryInterface::class =>  MySqlInvoiceStatusTransitionRepository::class,
        VendorRepositoryInterface::class => MySqlVendorRepository::class,
        UnitOfWorkInterface::class => MySqlUnitOfWork::class,
    ]);
};