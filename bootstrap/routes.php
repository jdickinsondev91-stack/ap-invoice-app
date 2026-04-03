<?php

$app->get('/invoices', App\Controllers\ListInvoicesController::class);
$app->get('/invoices/{id}', App\Controllers\GetInvoiceController::class);
$app->post('/invoices', App\Controllers\CreateInvoiceController::class);
