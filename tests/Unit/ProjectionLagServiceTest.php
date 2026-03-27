<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
declare(strict_types=1);

namespace App\Tests\Unit;

use App\InfrastructureInterface\PaymentProjectionRepositoryInterface;
use App\Service\ProjectionLagService;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class ProjectionLagServiceTest extends TestCase
{
    public function testSnapshotBuildsLagFromDataAndInfraTimestamps(): void
    {
        $connection = $this->createMock(Connection::class);
        $projection = $this->createMock(PaymentProjectionRepositoryInterface::class);

        $connection->expects(self::once())
            ->method('fetchOne')
            ->with('SELECT MAX(updated_at) FROM payment')
            ->willReturn('2025-11-08 10:00:05');

        $projection->expects(self::once())
            ->method('maxUpdatedAt')
            ->willReturn('2025-11-08 10:00:00');

        $service = new ProjectionLagService($connection, $projection);
        $snapshot = $service->snapshot();

        self::assertSame('2025-11-08 10:00:05', $snapshot['updatedAtData']);
        self::assertSame('2025-11-08 10:00:00', $snapshot['updatedAtInfra']);
        self::assertSame(5000, $snapshot['projectionLagMs']);
    }
}
