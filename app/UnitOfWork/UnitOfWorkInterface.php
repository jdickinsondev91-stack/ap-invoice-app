<?php 

namespace App\UnitOfWork;

interface UnitOfWorkInterface
{
    public function run(callable $callback): mixed;
}