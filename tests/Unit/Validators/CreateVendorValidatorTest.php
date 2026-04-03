<?php

namespace App\Tests\Unit\Validators;

use App\DTOs\CreateVendorDTO;
use App\Exceptions\ValidationException;
use App\Validators\CreateVendorValidator;
use PHPUnit\Framework\TestCase;

class CreateVendorValidatorTest extends TestCase
{
    private CreateVendorValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new CreateVendorValidator();
    }

    public function testValidEmailPassesValidation(): void
    {
        $this->expectNotToPerformAssertions();

        $this->validator->validate(new CreateVendorDTO('Acme Ltd', 'billing@acme.example'));
    }

    public function testThrowsForInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('not a valid email address');

        $this->validator->validate(new CreateVendorDTO('Acme Ltd', 'not-an-email'));
    }

    public function testThrowsForEmailWithoutDomain(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(new CreateVendorDTO('Acme Ltd', 'user@'));
    }

    public function testThrowsForEmailWithoutAtSymbol(): void
    {
        $this->expectException(ValidationException::class);

        $this->validator->validate(new CreateVendorDTO('Acme Ltd', 'notanemail.com'));
    }

    public function testExceptionMessageContainsInvalidEmail(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('bad-email');

        $this->validator->validate(new CreateVendorDTO('Acme Ltd', 'bad-email'));
    }
}
