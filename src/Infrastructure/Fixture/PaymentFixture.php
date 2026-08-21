<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Infrastructure\Fixture;

use App\Paying\Entity\PaymentEntity;
use App\Paying\ValueObject\PaymentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

/**
 * Seeds representative payment aggregates for local development and test flows.
 */
final class PaymentFixture extends Fixture implements FixtureGroupInterface
{
    /**
     * Loads fixture records into the persistence layer.
     */
    public function load(ObjectManager $manager): void
    {
        $faker = new PaymentFixtureFaker();
        $definitions = [
            ['payment-new', PaymentStatus::new, null],
            ['payment-processing', PaymentStatus::processing, 'stripe'],
            ['payment-completed', PaymentStatus::completed, 'stripe'],
            ['payment-failed', PaymentStatus::failed, 'stripe'],
            ['payment-refunded', PaymentStatus::refunded, 'paypal'],
        ];

        foreach ($definitions as [$reference, $status, $provider]) {
            $payment = new PaymentEntity(Uuid::v4(), $status, $faker->amount(), 'USD');
            if (null !== $provider) {
                $payment->withProviderRef($faker->providerReference($provider));
            }

            $manager->persist($payment);
            $this->addReference($reference, $payment);
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
