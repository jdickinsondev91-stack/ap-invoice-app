<?php 

namespace App\Repositories\InvoiceStatusTransition;

interface InvoiceStatusTransitionRepositoryInterface
{
    public function findTransitionsFrom(int $fromInvoiceStatusId): array;

    public function isValidTransition(int $fromInvoiceStatusId, int $toInvoiceStatusId): bool;
}