<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Fixture;

use App\Paying\Entity\PaymentWebhookLogEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Seeds webhook log records for local development and test scenarios.
 */
final class PaymentWebhookLogFixture extends Fixture implements FixtureGroupInterface
{
    /**
     * Loads fixture records into the persistence layer.
     */
    public function load(ObjectManager $manager): void
    {
        $definitions = [
            ['stripe', 'evt_fixture_stripe_completed', 'processed'],
            ['paypal', 'evt_fixture_paypal_refunded', 'received'],
        ];

        foreach ($definitions as [$provider, $externalEventId, $state]) {
            $log = new PaymentWebhookLogEntity($provider, $externalEventId, [
                'paymentId' => 'fixture-'.$provider,
                'externalEventId' => $externalEventId,
                'provider' => $provider,
            ]);

            if ('processed' === $state) {
                $log->markProcessed();
            }

            $manager->persist($log);
        }

        $manager->flush();
    }

    /**
     * Returns the fixture group names that enable this seed set.
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['payment'];
    }
}
