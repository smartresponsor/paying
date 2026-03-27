<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Functional\Fixture;

use App\Infrastructure\Fixture\PaymentFixture;
use App\Infrastructure\Fixture\PaymentGatewayFixture;
use App\Infrastructure\Fixture\PaymentMethodFixture;
use App\Infrastructure\Fixture\PaymentWebhookLogFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

final class PaymentFixtureDatasetSmokeTest extends TestCase
{
    public function testOwnedFixtureDatasetsHaveExpectedPersistCounts(): void
    {
        self::assertSame(5, $this->persistCount(new PaymentFixture()));
        self::assertSame(3, $this->persistCount(new PaymentGatewayFixture()));
        self::assertSame(3, $this->persistCount(new PaymentMethodFixture()));
        self::assertSame(2, $this->persistCount(new PaymentWebhookLogFixture()));
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
            $metadata = $this->createStub(ClassMetadata::class);
            $metadata->method('getName')->willReturn($class);

            return $metadata;
        });
        $unitOfWork = new class {
            public function isInIdentityMap(object $entity): bool
            {
                return false;
            }
        };
        $manager->method('getUnitOfWork')->willReturn($unitOfWork);

        if (method_exists($fixture, 'setReferenceRepository')) {
            $fixture->setReferenceRepository(new ReferenceRepository($manager));
        }

        $fixture->load($manager);

        return $count;
    }
}
