<?php

namespace App\Tests\Integration\Repositories;

use App\Models\InvoiceStatusTransition;
use App\Repositories\InvoiceStatusTransition\MySqlInvoiceStatusTransitionRepository;
use App\Tests\Integration\DatabaseTestCase;

class InvoiceStatusTransitionRepositoryTest extends DatabaseTestCase
{
    private MySqlInvoiceStatusTransitionRepository $repo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = new MySqlInvoiceStatusTransitionRepository($this->db);
    }

    public function testFindTransitionsFromPendingReturnsTwoOptions(): void
    {
        // Pending (1) -> Approved (2) or Rejected (4)
        $transitions = $this->repo->findTransitionsFrom(1);

        $this->assertCount(2, $transitions);
        $this->assertContainsOnlyInstancesOf(InvoiceStatusTransition::class, $transitions);
    }

    public function testFindTransitionsFromPendingContainsExpectedTargets(): void
    {
        $transitions = $this->repo->findTransitionsFrom(1);

        $targets = array_map(fn(InvoiceStatusTransition $t) => $t->toInvoiceStatusId, $transitions);
        sort($targets);

        $this->assertSame([2, 4], $targets);
    }

    public function testFindTransitionsFromApprovedReturnsTwoOptions(): void
    {
        // Approved (2) -> Paid (3) or Rejected (4)
        $transitions = $this->repo->findTransitionsFrom(2);

        $this->assertCount(2, $transitions);
    }

    public function testFindTransitionsFromPaidReturnsEmpty(): void
    {
        // Paid (3) is a terminal state
        $transitions = $this->repo->findTransitionsFrom(3);

        $this->assertSame([], $transitions);
    }

    public function testFindTransitionsFromRejectedReturnsEmpty(): void
    {
        // Rejected (4) is a terminal state
        $transitions = $this->repo->findTransitionsFrom(4);

        $this->assertSame([], $transitions);
    }

    public function testIsValidTransitionReturnsTrueForAllowedTransition(): void
    {
        $this->assertTrue($this->repo->isValidTransition(1, 2)); // Pending -> Approved
        $this->assertTrue($this->repo->isValidTransition(1, 4)); // Pending -> Rejected
        $this->assertTrue($this->repo->isValidTransition(2, 3)); // Approved -> Paid
        $this->assertTrue($this->repo->isValidTransition(2, 4)); // Approved -> Rejected
    }

    public function testIsValidTransitionReturnsFalseForDisallowedTransition(): void
    {
        $this->assertFalse($this->repo->isValidTransition(1, 3)); // Pending -> Paid (not allowed)
        $this->assertFalse($this->repo->isValidTransition(3, 1)); // Paid -> Pending (not allowed)
        $this->assertFalse($this->repo->isValidTransition(4, 1)); // Rejected -> Pending (not allowed)
        $this->assertFalse($this->repo->isValidTransition(2, 1)); // Approved -> Pending (not allowed)
    }

    public function testIsValidTransitionReturnsFalseForNonExistentStatus(): void
    {
        $this->assertFalse($this->repo->isValidTransition(1, 999));
        $this->assertFalse($this->repo->isValidTransition(999, 1));
    }
}
