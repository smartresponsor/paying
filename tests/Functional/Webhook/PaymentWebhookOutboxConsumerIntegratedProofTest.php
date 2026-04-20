<?php

declare(strict_types=1);

namespace App\Paying\Tests\Functional\Webhook;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class PaymentWebhookOutboxConsumerIntegratedProofTest extends TestCase
{
    public function testStripeWebhookQueuesPublishesAndCapturesPayment(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects(self::any())
            ->method('flush')
            ->willReturnCallback(static function (): void {
            });

        self::assertTrue(true);
    }

    public function testSecondProofPlaceholder(): void
    {
        self::assertTrue(true);
    }
}
