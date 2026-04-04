<?php 

namespace App\Models;

use App\Exceptions\MissingRowKeyException;

abstract class Model implements ModelInterface
{
    protected static function assertRowKeys(array $row, array $keys): void
    {
        foreach ($keys as $key) { 
            if (!array_key_exists($key, $row)) {
                throw new MissingRowKeyException($key, static::class);
            }
        }
    }
}