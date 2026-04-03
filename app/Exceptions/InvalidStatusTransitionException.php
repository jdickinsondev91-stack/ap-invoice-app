<?php

namespace App\Exceptions;

class InvalidStatusTransitionException extends \RuntimeException
{
    public function __construct(string $fromSlug, string $toSlug)
    {
        parent::__construct("Cannot transition invoice status from '{$fromSlug}' to '{$toSlug}'.");
    }
}
