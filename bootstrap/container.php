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
use FrameworkX\Container;
use React\Mysql\MysqlClient;

$db = new MysqlClient(
    getenv('DB_USER') . ':' .
    getenv('DB_PASSWORD') . '@' .
    getenv('DB_HOST') . ':' .
    getenv('DB_PORT') . '/' .
    getenv('DB_NAME')
);

return new Container([
    MysqlClient::class => fn() => $db,
    DuplicateInvoiceFlagRepositoryInterface::class => fn(MysqlClient $db) => new MySqlDuplicateInvoiceFlagRepository($db),
    InvoiceRepositoryInterface::class => fn(MysqlClient $db) => new MySqlInvoiceRepository($db),
    InvoiceItemRepositoryInterface::class => fn(MysqlClient $db) => new MySqlInvoiceItemRepository($db),
    InvoiceStatusRepositoryInterface::class => fn(MysqlClient $db) => new MySqlInvoiceStatusRepository($db),
    InvoiceStatusHistoryRepositoryInterface::class => fn(MysqlClient $db) => new MySqlInvoiceStatusHistoryRepository($db),
    InvoiceStatusTransitionRepositoryInterface::class => fn(MysqlClient $db) => new MySqlInvoiceStatusTransitionRepository($db),
    VendorRepositoryInterface::class => fn(MysqlClient $db) => new MySqlVendorRepository($db),
]);
