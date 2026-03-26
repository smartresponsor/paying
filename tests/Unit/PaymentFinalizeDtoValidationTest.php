<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Controller\Dto\PaymentConsoleFinalizeRequestDto;
use App\Controller\Dto\PaymentFinalizeRequestDto;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PaymentFinalizeDtoValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function testApiFinalizeDtoAllowsKnownStatusAndEmptyStatus(): void
    {
        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = 'internal';
        $dto->status = 'completed';

        self::assertCount(0, $this->validator->validate($dto));

        $dto->status = '';
        self::assertCount(0, $this->validator->validate($dto));
    }

    public function testApiFinalizeDtoRejectsUnknownStatus(): void
    {
        $dto = new PaymentFinalizeRequestDto();
        $dto->provider = 'internal';
        $dto->status = 'done';

        $violations = $this->validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
        self::assertSame('status', (string) $violations[0]->getPropertyPath());
    }

    public function testConsoleFinalizeDtoRejectsUnknownStatus(): void
    {
        $dto = new PaymentConsoleFinalizeRequestDto();
        $dto->paymentId = '01HZY9M8Q6M7X4YH3B2A1C0D9E';
        $dto->provider = 'internal';
        $dto->status = 'done';

        $violations = $this->validator->validate($dto);

        self::assertGreaterThan(0, $violations->count());
    }
}
