<?php

declare(strict_types=1);

namespace App\Paying\Tests\Unit\Form;

use App\Paying\Form\PaymentConsoleFinalizeType;
use App\Paying\Form\PaymentConsoleRefundType;
use App\Paying\Form\PaymentCreateType;
use App\Paying\Form\PaymentFinalizeType;
use App\Paying\Form\PaymentStartType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class PaymentFormsTest extends TestCase
{
    #[Test]
    public function paymentFormsExposeConsoleRelevantFields(): void
    {
        $factory = Forms::createFormFactory();

        self::assertSame(['orderId', 'amountMinor', 'currency'], array_keys($factory->create(PaymentCreateType::class)->all()));
        self::assertSame(['orderId', 'amount', 'currency', 'provider'], array_keys($factory->create(PaymentStartType::class)->all()));
        self::assertSame(['provider', 'providerRef', 'gatewayTransactionId', 'status'], array_keys($factory->create(PaymentFinalizeType::class)->all()));
        self::assertSame(['paymentId', 'amount', 'provider'], array_keys($factory->create(PaymentConsoleRefundType::class)->all()));
        self::assertSame(['paymentId', 'provider', 'providerRef', 'providerTransactionId', 'status'], array_keys($factory->create(PaymentConsoleFinalizeType::class)->all()));
    }
}
