<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Infrastructure\Payment\Fixture;

use App\Entity\Payment\PaymentMethod;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Ulid;

final class PaymentMethodFixture extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        foreach (['card', 'paypal', 'bank_transfer'] as $code) {
            $method = new PaymentMethod((new Ulid())->toRfc4122(), $code);
            $manager->persist($method);
            $this->addReference('payment-method-'.$code, $method);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['payment'];
    }
}
