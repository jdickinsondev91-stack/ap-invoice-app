<?php 

namespace App\Exceptions;

class MissingRowKeyException extends \RuntimeException
{
    public function __construct(string $key, string $model)
    {
        parent::__construct(
            "Missing required key '$key' in row data for {$model}"
        );
    }
}