<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment\Fixture;

use App\Entity\Payment\PaymentWebhookLog;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

final class PaymentWebhookLogFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $definitions = [
            ['stripe', 'evt_fixture_stripe_completed', 'processed'],
            ['paypal', 'evt_fixture_paypal_refunded', 'received'],
        ];

        foreach ($definitions as [$provider, $externalEventId, $state]) {
            $log = new PaymentWebhookLog($provider, $externalEventId, [
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

    public static function getGroups(): array
    {
        return ['payment'];
    }
}
