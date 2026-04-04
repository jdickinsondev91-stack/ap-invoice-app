<?php

namespace App\Validators;

use App\DTOs\CreateVendorDTO;
use App\Exceptions\ValidationException;

class CreateVendorValidator
{
    public function validate(CreateVendorDTO $dto): void
    {
        if (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException("'{$dto->email}' is not a valid email address.");
        }
    }
}
