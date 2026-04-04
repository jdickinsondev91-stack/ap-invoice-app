<?php

use App\Controllers\CreateInvoiceController;
use App\Controllers\CreateVendorController;
use App\Controllers\GetInvoiceController;
use App\Controllers\GetInvoiceHistoryController;
use App\Controllers\GetVendorController;
use App\Controllers\ListInvoicesController;
use App\Controllers\ListInvoiceStatusesController;
use App\Controllers\ListVendorsController;
use App\Controllers\UpdateInvoiceStatusController;
use App\Middleware\JsonRequestMiddleware;
use FrameworkX\App;

return function (App $app): void {
    $app->get('/vendors', ListVendorsController::class);
    $app->get('/vendors/{id}', GetVendorController::class);
    $app->post('/vendors', JsonRequestMiddleware::class, CreateVendorController::class);

    $app->get('/invoice-statuses', ListInvoiceStatusesController::class);

    $app->get('/invoices', ListInvoicesController::class);
    $app->get('/invoices/{id}', GetInvoiceController::class);
    $app->post('/invoices', JsonRequestMiddleware::class, CreateInvoiceController::class);
    $app->patch('/invoices/{id}/status', JsonRequestMiddleware::class, UpdateInvoiceStatusController::class);
    $app->get('/invoices/{id}/history', GetInvoiceHistoryController::class);
};
