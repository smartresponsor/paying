<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Tests\Payment\Functional\Fixture;

use App\Infrastructure\Payment\Fixture\PaymentFixture;
use App\Infrastructure\Payment\Fixture\PaymentGatewayFixture;
use App\Infrastructure\Payment\Fixture\PaymentMethodFixture;
use App\Infrastructure\Payment\Fixture\PaymentWebhookLogFixture;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;
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
        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::any())
            ->method('persist')
            ->willReturnCallback(static function () use (&$count): void {
                ++$count;
            });
        $manager->expects(self::once())->method('flush');

        if (method_exists($fixture, 'setReferenceRepository')) {
            $fixture->setReferenceRepository(new ReferenceRepository($manager));
        }

        $fixture->load($manager);

        return $count;
    }
}
