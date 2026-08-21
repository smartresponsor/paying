<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Paying\Tests\Functional\Fixture;

use App\Paying\Entity\PaymentEntity;
use App\Paying\Infrastructure\Fixture\PaymentFixture;
use App\Paying\Infrastructure\Fixture\PaymentGatewayFixture;
use App\Paying\Infrastructure\Fixture\PaymentMethodFixture;
use App\Paying\Infrastructure\Fixture\PaymentWebhookLogFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Exercises the payment fixture dataset smoke scenario within the payment fixture test surface.
 */
final class PaymentFixtureDatasetSmokeTest extends TestCase
{
    /**
     * Verifies that owned fixture datasets have expected persist counts.
     */
    public function testOwnedFixtureDatasetsHaveExpectedPersistCounts(): void
    {
        self::assertSame(5, $this->persistCount(new PaymentFixture()));
        self::assertSame(3, $this->persistCount(new PaymentGatewayFixture()));
        self::assertSame(3, $this->persistCount(new PaymentMethodFixture()));
        self::assertSame(2, $this->persistCount(new PaymentWebhookLogFixture()));
    }

    public function testPaymentFixtureUsesRealUuidSlugs(): void
    {
        $payments = $this->persistedPayments(new PaymentFixture());

        self::assertCount(5, $payments);

        foreach ($payments as $payment) {
            self::assertInstanceOf(PaymentEntity::class, $payment);
            self::assertTrue(Uuid::isValid($payment->slug()));
            self::assertNotSame('7c4f1c2e-9e33-4c1b-9a6f-1a2b3c4d5011', $payment->slug());
        }
    }

    public function testGatewayAndMethodFixturesUseRealUuidSlugs(): void
    {
        $gateways = $this->persistedGatewayLikeEntities(new PaymentGatewayFixture());
        $methods = $this->persistedGatewayLikeEntities(new PaymentMethodFixture());

        self::assertCount(3, $gateways);
        self::assertCount(3, $methods);

        foreach ([$gateways, $methods] as $entities) {
            foreach ($entities as $entity) {
                self::assertTrue(method_exists($entity, 'slug'));
                self::assertTrue(Uuid::isValid($entity->slug()));
                self::assertNotSame('7c4f1c2e-9e33-4c1b-9a6f-1a2b3c4d5021', $entity->slug());
            }
        }
    }

    private function persistCount(object $fixture): int
    {
        $count = 0;
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects(self::any())
            ->method('persist')
            ->willReturnCallback(static function () use (&$count): void {
                ++$count;
            });
        $manager->expects(self::once())->method('flush');
        $manager->method('getClassMetadata')->willReturnCallback(function (string $class): ClassMetadata {
            $metadata = $this->createMock(ClassMetadata::class);
            $metadata->method('getName')->willReturn($class);

            return $metadata;
        });
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->method('isInIdentityMap')->willReturn(false);
        $manager->method('getUnitOfWork')->willReturn($unitOfWork);

        if (method_exists($fixture, 'setReferenceRepository')) {
            $fixture->setReferenceRepository(new ReferenceRepository($manager));
        }

        $fixture->load($manager);

        return $count;
    }

    /**
     * @return list<PaymentEntity>
     */
    private function persistedPayments(object $fixture): array
    {
        $payments = [];
        $this->loadFixtureAndCapture($fixture, static function (object $entity) use (&$payments): void {
            if ($entity instanceof PaymentEntity) {
                $payments[] = $entity;
            }
        });

        return $payments;
    }

    /**
     * @return list<object>
     */
    private function persistedGatewayLikeEntities(object $fixture): array
    {
        $entities = [];
        $this->loadFixtureAndCapture($fixture, static function (object $entity) use (&$entities): void {
            $entities[] = $entity;
        });

        return $entities;
    }

    private function loadFixtureAndCapture(object $fixture, \Closure $onPersist): void
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $manager->expects(self::any())
            ->method('persist')
            ->willReturnCallback($onPersist);
        $manager->expects(self::once())->method('flush');
        $manager->method('getClassMetadata')->willReturnCallback(function (string $class): ClassMetadata {
            $metadata = $this->createMock(ClassMetadata::class);
            $metadata->method('getName')->willReturn($class);

            return $metadata;
        });
        $unitOfWork = $this->createMock(UnitOfWork::class);
        $unitOfWork->method('isInIdentityMap')->willReturn(false);
        $manager->method('getUnitOfWork')->willReturn($unitOfWork);

        if (method_exists($fixture, 'setReferenceRepository')) {
            $fixture->setReferenceRepository(new ReferenceRepository($manager));
        }

        $fixture->load($manager);
    }
}
