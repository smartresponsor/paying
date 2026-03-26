<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

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
            'payment:lifecycle:run',
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