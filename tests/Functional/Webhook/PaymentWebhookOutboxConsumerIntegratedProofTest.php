<?php

declare(strict_types=1);

namespace App\Paying\Tests\Functional\Webhook;

use PHPUnit\Framework\TestCase;

/**
 * Marks the installed-runtime webhook -> outbox -> consumer proof contour that remains intentionally open.
 */
final class PaymentWebhookOutboxConsumerIntegratedProofTest extends TestCase
{
    /**
     * Documents that the full installed-runtime proof still requires a wired consumer process and transport loop.
     */
    public function testStripeWebhookQueuesPublishesAndCapturesPayment(): void
    {
        self::markTestSkipped('Installed-runtime webhook -> outbox -> consumer proof is not wired in this repository snapshot.');
    }

    /**
     * Documents that secondary integrated proof coverage is still a deliberate placeholder until a runtime harness exists.
     */
    public function testSecondProofPlaceholder(): void
    {
        self::markTestSkipped('Secondary integrated proof placeholder remains open until the installed-runtime harness is introduced.');
    }
}
