<?php

$app->get('/vendors', App\Controllers\ListVendorsController::class);
$app->get('/vendors/{id}', App\Controllers\GetVendorController::class);
$app->post('/vendors', App\Controllers\CreateVendorController::class);

$app->get('/invoice-statuses', App\Controllers\ListInvoiceStatusesController::class);

$app->get('/invoices', App\Controllers\ListInvoicesController::class);
$app->get('/invoices/{id}', App\Controllers\GetInvoiceController::class);
$app->post('/invoices', App\Controllers\CreateInvoiceController::class);
$app->patch('/invoices/{id}/status', App\Controllers\UpdateInvoiceStatusController::class);
$app->get('/invoices/{id}/history', App\Controllers\GetInvoiceHistoryController::class);
