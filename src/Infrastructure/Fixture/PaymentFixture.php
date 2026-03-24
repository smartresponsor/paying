<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

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
        $definitions = [
            ['payment-new', PaymentStatus::new, '15.00', 'USD', null],
            ['payment-processing', PaymentStatus::processing, '24.99', 'USD', 'stripe_pi_processing'],
            ['payment-completed', PaymentStatus::completed, '99.00', 'USD', 'stripe_pi_completed'],
            ['payment-failed', PaymentStatus::failed, '42.00', 'USD', 'stripe_pi_failed'],
            ['payment-refunded', PaymentStatus::refunded, '12.50', 'USD', 'paypal_refunded_1001'],
        ];

        foreach ($definitions as [$reference, $status, $amount, $currency, $providerRef]) {
            $payment = new Payment(new Ulid(), $status, $amount, $currency);
            if (null !== $providerRef) {
                $payment->withProviderRef($providerRef);
            }
            $manager->persist($payment);
            $this->addReference($reference, $payment);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['payment'];
    }
}
