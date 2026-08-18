<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Fixture;

use App\Paying\Entity\PaymentMethodEntity;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * Seeds payment method reference data for local development and tests.
 */
final class PaymentMethodFixture extends Fixture implements FixtureGroupInterface
{
    /**
     * Loads fixture records into the persistence layer.
     */
    public function load(ObjectManager $manager): void
    {
        foreach (['card', 'paypal', 'bank_transfer'] as $code) {
            $method = new PaymentMethodEntity(Uuid::v4()->toRfc4122(), $code);
            $manager->persist($method);
            $this->addReference('payment-method-'.$code, $method);
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
