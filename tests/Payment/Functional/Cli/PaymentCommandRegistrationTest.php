<?php

declare(strict_types=1);

// Marketing America Corp. Oleksandr Tishchenko

namespace App\Tests\Functional\Cli;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaymentCommandRegistrationTest extends KernelTestCase
{
    public function testPaymentOperationalCommandsAreRegistered(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $expected = [
            'payment:dlq:replay',
            'payment:gate:slo',
            'payment:idem:purge',
            'payment:outbox:run',
            'payment:projection:rebuild',
            'payment:projection:sync',
            'payment:reconcile:run',
            'payment:sla:report',
        ];

        foreach ($expected as $command) {
            self::assertTrue($application->has($command), sprintf('Expected command "%s" to be registered.', $command));
        }
    }
}
