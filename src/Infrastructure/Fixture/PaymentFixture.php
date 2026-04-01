<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Infrastructure\Fixture;

use App\Entity\Payment;
use App\ValueObject\PaymentStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Ulid;

final class PaymentFixture extends Fixture implements FixtureGroupInterface
{
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
            $payment = new Payment(new Ulid(), $status, $faker->amount(), 'USD');
            if (null !== $provider) {
                $payment->withProviderRef($faker->providerReference($provider));
            }

            $manager->persist($payment);
            $this->addReference($reference, $payment);
        }

        $manager->flush();
    }

    /**
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['payment'];
    }
}
