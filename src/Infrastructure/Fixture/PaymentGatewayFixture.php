<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Fixture;

use App\Paying\Entity\PaymentGatewayEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * Seeds payment gateway reference data for local development and tests.
 */
final class PaymentGatewayFixture extends Fixture implements FixtureGroupInterface
{
    /**
     * Loads fixture records into the persistence layer.
     */
    public function load(ObjectManager $manager): void
    {
        foreach (['internal', 'stripe', 'paypal'] as $code) {
            $gateway = new PaymentGatewayEntity(Uuid::v4()->toRfc4122(), $code);
            $manager->persist($gateway);
            $this->addReference('payment-gateway-'.$code, $gateway);
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
