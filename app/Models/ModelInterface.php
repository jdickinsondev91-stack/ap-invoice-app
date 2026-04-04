<?php

namespace App\Models;

interface ModelInterface
{
    public static function fromRow(array $row): static;

    public function toArray(): array;   
}